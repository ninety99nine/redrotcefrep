<?php

namespace App\Services;

use Exception;
use App\Models\Tag;
use App\Models\Store;
use League\Csv\Reader;
use App\Enums\TagType;
use App\Models\Product;
use App\Models\Category;
use App\Enums\Association;
use App\Enums\ProductType;
use Illuminate\Support\Str;
use App\Enums\SortProductBy;
use App\Enums\ProductUnitType;
use App\Enums\UploadFolderName;
use App\Enums\StockQuantityType;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResources;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Product\CreateProductRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductService extends BaseService
{
    /**
     * Show products.
     *
     * @param array $data
     * @return ProductResources|BinaryFileResponse|array
     */
    public function showProducts(array $data): ProductResources|BinaryFileResponse|array
    {
        $tagId = $data['tag_id'] ?? null;
        $storeId = $data['store_id'] ?? null;
        $categoryIds = $data['category_ids'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = Product::isNotVariant();
        }else if($association == Association::TEAM_MEMBER) {
            $query = Product::isNotVariant()->where('store_id', $storeId);
        }elseif($tagId) {
            $query = Product::whereHas('tags', function (Builder $query) use ($tagId) {
                $query->where('id', $tagId);
            });
        }else {
            $query = Product::isNotVariant()->where('store_id', $storeId)->visible();
        }

        if($categoryIds) $query = $query->whereHas('categories', function (Builder $query) use ($categoryIds) {
            $query->whereIn('id', $categoryIds);
        });

        if($storeId) $query = $query->where('store_id', $storeId);

        if($tagId) {
            $query = $query->when(!request()->has('_sort'), function ($query) use ($tagId) {
                return $query->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                            ->where('product_tag.tag_id', $tagId)
                            ->orderBy('product_tag.updated_at', 'desc')
                            ->select('products.*');
            });
        }else{
            $query = $query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create product.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createProduct(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::findOrFail($storeId);

        $tags = $data['tags'] ?? null;
        $categories = $data['categories'] ?? null;
        unset($data['tags'], $data['categories']);

        $deliveryMethodIds = $data['delivery_method_ids'] ?? null;

        $data = array_merge($data, [
            'currency' => $store->currency
        ]);

        $product = Product::create($data);

        if(!is_null($tags)) {
            $this->createProductTags($product, $tags);
        }

        if(!is_null($categories)) {
            $this->createProductCategories($product, $categories);
        }else{
            $this->createProductCategories($product, [StoreService::$defaultCategoryName]);
        }

        if(!is_null($deliveryMethodIds)) {
            $product->deliveryMethods()->sync($deliveryMethodIds);
        }

        $this->updateProductArrangement([
            'store_id' => $storeId,
            'product_ids' => [$product->id]
        ]);

        // Create product photo if provided
        if (isset($data['photo']) && !empty($data['photo'])) {

            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_type' => 'product',
                'mediable_id' => $product->id,
                'upload_folder_name' => UploadFolderName::PRODUCT_PHOTO->value
            ]);

        }

        return $this->showCreatedResource($product);
    }

    /**
     * Update multiple products.
     *
     * @param array $data
     * @return array
     */
    public function updateProducts(array $data): array
    {
        $storeId = $data['store_id'];
        $productsData = $data['products'] ?? [];
        $tagsToAdd = $data['tags_to_add'] ?? null;
        $globalVisible = $data['visible'] ?? null;
        $tagsToRemove = $data['tags_to_remove'] ?? null;
        $categoriesToAdd = $data['categories_to_add'] ?? null;
        $categoriesToRemove = $data['categories_to_remove'] ?? null;

        $totalProducts = 0;

        foreach ($productsData as $productData) {

            $product = Product::where('id', $productData['id'])
                ->where('store_id', $storeId)
                ->first();

            if (!$product) {
                continue;
            }

            // Merge global visible setting if provided
            if (!is_null($globalVisible)) {
                $productData['visible'] = $globalVisible;
            }

            // Filter fillable fields
            $fillableData = array_intersect_key(
                $productData,
                array_flip($product->getFillable())
            );

            // Update product with fillable data
            $product->update($fillableData);

            $tags = $productData['tags'] ?? null;
            $categories = $productData['categories'] ?? null;

            if(!is_null($tags)) {
                $this->createProductTags($product, $tags);
            }

            if(!is_null($categories)) {
                $this->createProductCategories($product, $categories);
            }

            // Handle tags to add
            if (!is_null($tagsToAdd)) {
                $product->tags()->syncWithoutDetaching($tagsToAdd);
            }

            // Handle tags to remove
            if (!is_null($tagsToRemove)) {
                $product->tags()->detach($tagsToRemove);
            }

            // Handle categories to add
            if (!is_null($categoriesToAdd)) {
                $product->categories()->syncWithoutDetaching($categoriesToAdd);
            }

            // Handle categories to remove
            if (!is_null($categoriesToRemove)) {
                $product->categories()->detach($categoriesToRemove);
            }

            // Handle delivery methods if provided
            if (isset($productData['delivery_method_ids']) && !is_null($productData['delivery_method_ids'])) {
                $product->deliveryMethods()->sync($productData['delivery_method_ids']);
            }

            $totalProducts = $totalProducts + 1;
        }

        return ['updated' => true, 'message' => $totalProducts . ($totalProducts == 1 ? ' product': ' products') . ' updated'];
    }

    /**
     * Delete Products.
     *
     * @param array $productIds
     * @return array
     * @throws Exception
     */
    public function deleteProducts(array $productIds): array
    {
        $products = Product::whereIn('id', $productIds)->get();

        if ($totalProducts = $products->count()) {

            foreach ($products as $product) {

                $this->deleteProduct($product);

            }

            return ['message' => $totalProducts . ($totalProducts == 1 ? ' Product' : ' Products') . ' deleted'];

        } else {
            throw new Exception('No Products deleted');
        }
    }

    /**
     * Import products from CSV.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function importProducts(array $data): array
    {
        $errors = [];
        $file = $data['file'];
        $storeId = $data['store_id'];
        $store = Store::findOrFail($storeId);

        // Preload existing products for the store
        $existingProducts = Product::where('store_id', $storeId)
            ->select('id', 'name')->get()
            ->pluck('id', 'name')
            ->toArray();

        // Map to store pending parent products (name => UUID)
        $pendingParents = [];

        // Map to store CSV record IDs by name (name => ID)
        $csvRecordIds = [];

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        // Create non-variants (parents) first
        DB::beginTransaction();

        try {

            $totalProducts = 0;
            $productsToCreate = [];

            // First pass: Collect all CSV rows and map parent names
            foreach ($records as $index => $record) {

                try {

                    $id = empty($record['ID'] ?? null) ? null : $record['ID'];
                    $id = ($id && Str::isUuid($id)) ? $id : Str::uuid()->toString();
                    $name = empty($record['Name'] ?? null) ? null : $record['Name'];

                    if (!$name) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => ['Name is required']
                        ];
                        continue;
                    }

                    $parentName = $record['Parent Name'] ?? null;
                    $parentProductId = null;

                    if ($parentName) {

                        // Check existing products or productsToCreate by name
                        if (isset($existingProducts[$parentName])) {

                            $parentProductId = $existingProducts[$parentName];

                        } else {

                            // Assign UUID for parent if not found
                            if (!isset($pendingParents[$parentName])) {

                                $pendingParents[$parentName] = Str::uuid()->toString();

                            }

                            $parentProductId = $pendingParents[$parentName];

                        }
                    }

                    $productData = [
                        'row' => $index,

                        'id' => $id,
                        'name' => $name,
                        'store_id' => $storeId,
                        'currency' => $store->currency,
                        'parent_product_id' => $parentProductId,

                        'is_free' => $this->parseTruthy($record['Free'] ?? false),
                        'visible' => $this->parseTruthy($record['Visible'] ?? true),
                        'tax_overide' => $this->parseTruthy($record['Tax Override'] ?? false),
                        'show_description' => $this->parseTruthy($record['Show Description'] ?? false),
                        'is_estimated_price' => $this->parseTruthy($record['Estimated Price'] ?? false),
                        'set_daily_capacity' => $this->parseTruthy($record['Set Daily Capacity'] ?? false),
                        'show_price_per_unit' => $this->parseTruthy($record['Show Price Per Unit'] ?? false),
                        'set_min_order_quantity' => $this->parseTruthy($record['Set Min Order Quantity'] ?? false),
                        'set_max_order_quantity' => $this->parseTruthy($record['Set Max Order Quantity'] ?? false),

                        'unit_value' => empty($record['Weight'] ?? null) ? '0.00' : floatval($record['Weight']),
                        'unit_weight' => empty($record['Unit Value'] ?? null) ? '1' : floatval($record['Unit Value']),
                        'unit_sale_price' => empty($record['Sale Price'] ?? null) ? '0.00' : floatval($record['Sale Price']),
                        'unit_cost_price' => empty($record['Cost Price'] ?? null) ? '0.00' : floatval($record['Cost Price']),
                        'unit_regular_price' => empty($record['Regular Price'] ?? null) ? '0.00' : floatval($record['Regular Price']),
                        'tax_overide_amount' => empty($record['Tax Override Amount'] ?? null) ? '0.00' : floatval($record['Tax Override Amount']),

                        'position' => empty($record['Position'] ?? null) ? '1' : intval($record['Position']),
                        'daily_capacity' => empty($record['Daily Capacity'] ?? null) ? '1' : intval($record['Daily Capacity']),
                        'stock_quantity' => empty($record['Stock Quantity'] ?? null) ? '100' : intval($record['Stock Quantity']),
                        'min_order_quantity' => empty($record['Min Order Quantity'] ?? null) ? '1' : intval($record['Min Order Quantity']),
                        'max_order_quantity' => empty($record['Max Order Quantity'] ?? null) ? '1' : intval($record['Max Order Quantity']),

                        'type' => empty($record['Type'] ?? null) ? ProductType::PHYSICAL->value : $record['Type'],
                        'unit_type' => empty($record['Unit Type'] ?? null) ? ProductUnitType::QUANTITY->value : $record['Unit Type'],
                        'stock_quantity_type' => empty($record['Stock Type'] ?? null) ? StockQuantityType::UNLIMITED->value : $record['Stock Type'],

                        'sku' => empty($record['Sku'] ?? null) ? null : $record['Sku'],
                        'barcode' => empty($record['Barcode'] ?? null) ? null : $record['Barcode'],
                        'description' => empty($record['Description'] ?? null) ? null : $record['Description'],
                        'download_link' => empty($record['Download Link'] ?? null) ? null : $record['Download Link'],

                        'tags' => empty($record['Tags'] ?? null) ? null : array_map('trim', explode(',', $record['Tags'])),
                        'categories' => empty($record['Categories'] ?? null) ? null : array_map('trim', explode(',', $record['Categories'])),

                    ];

                    // Validate product data
                    $validator = validator($productData, (new CreateProductRequest)->rules(), (new CreateProductRequest)->messages());

                    if ($validator->fails()) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    $csvRecordIds[$name] = $record['ID'] ?? null;
                    $productsToCreate[] = $productData;
                    $totalProducts++;

                } catch (Exception $e) {

                    $errors[] = [
                        'row' => $index,
                        'messages' => [$e->getMessage()]
                    ];

                }

            }

            if (!empty($errors)) {
                throw new Exception('Validation errors occurred: ' . implode('; ', $errors));
            }

            // Second pass: Handle pending parents
            foreach ($pendingParents as $parentName => $parentId) {

                $parentData = null;
                $parentIndex = null;

                // Find parent in productsToCreate by name
                foreach ($productsToCreate as $index => $product) {
                    if ($product['name'] === $parentName) {
                        $parentData = $product;
                        $parentIndex = $index;
                        break;
                    }
                }

                if ($parentData) {

                    // Preserve CSV-provided ID if present and a valid UUID, otherwise use parentId
                    $csvId = $csvRecordIds[$parentName] ?? null;
                    $finalParentId = ($csvId && Str::isUuid($csvId)) ? $csvId : $parentId;

                    if ($parentData['id'] !== $finalParentId) {
                        $productsToCreate[$parentIndex]['id'] = $finalParentId;
                    }

                    // Update variants to reference the correct parent ID
                    $productsToCreate = array_map(function ($product) use ($parentId, $finalParentId) {
                        if ($product['parent_product_id'] === $parentId) {
                            $product['parent_product_id'] = $finalParentId;
                        }
                        return $product;
                    }, $productsToCreate);

                } else {

                    // Parent not in CSV, create minimal parent
                    $parentData = [
                        'id' => $parentId,
                        'name' => $parentName,
                        'store_id' => $storeId,
                        'currency' => $store->currency,
                    ];

                    $validator = validator($parentData, (new CreateProductRequest)->rules(), (new CreateProductRequest)->messages());

                    if ($validator->fails()) {

                        $keys = [];

                        //  Set errors on the variants since this is a non existent product
                        foreach ($productsToCreate as $key => $product) {
                            if ($product['parent_product_id'] === $parentId) {
                                $keys[] = $key;
                                $errors[] = [
                                    'row' => $product['row'],
                                    'messages' => $validator->errors()->all()
                                ];
                                $totalProducts--;
                            }
                        }

                        //  Remove variants from the products to create
                        $productsToCreate = collect($productsToCreate)->filter(function ($value, int $key) use ($keys) {
                            return !in_array($key, $keys);
                        })->all();

                        continue;
                    }

                    $productsToCreate[] = $parentData;
                    $totalProducts++;

                }
            }

            // Split products into non-variants and variants
            $nonVariants = [];
            $variants = [];

            foreach ($productsToCreate as $productData) {
                if (is_null($productData['parent_product_id'])) {
                    $nonVariants[] = $productData;
                } else {
                    $variants[] = $productData;
                }
            }

            try {

                foreach ($nonVariants as $productData) {

                    $product = Product::updateOrCreate(
                        ['id' => $productData['id']],
                        $productData
                    );

                    // Handle tags
                    if (!empty($productData['tags'])) {
                        $this->createProductTags($product, $productData['tags']);
                    }

                    // Handle categories
                    if (!empty($productData['categories'])) {
                        $this->createProductCategories($product, $productData['categories']);
                    }

                }

            } catch (\Exception $e) {

                throw new Exception('Failed to create parent products: ' . $e->getMessage());

            }

            try {

                foreach ($variants as $productData) {

                    $product = Product::updateOrCreate(
                        ['id' => $productData['id']],
                        $productData
                    );

                    // Handle tags
                    if (!empty($productData['tags'])) {
                        $this->createProductTags($product, $productData['tags']);
                    }

                    // Handle categories
                    if (!empty($productData['categories'])) {
                        $this->createProductCategories($product, $productData['categories']);
                    }

                }

            } catch (Exception $e) {

                throw new Exception('Failed to create variant products: ' . $e->getMessage());

            }

            // Sort productsToCreate by 'position' ascending, defaulting to PHP_INT_MAX for null
            usort($productsToCreate, function ($a, $b) {
                $aPosition = $a['position'] ?? PHP_INT_MAX;
                $bPosition = $b['position'] ?? PHP_INT_MAX;
                return $aPosition <=> $bPosition;
            });

            // Extract 'id' from the sorted array
            $productIds = array_column($productsToCreate, 'id');

            if (!empty($productIds)) {
                $this->updateProductArrangement([
                    'store_id' => $storeId,
                    'product_ids' => $productIds
                ]);
            }

            DB::commit();

            return [
                'message' => $totalProducts . ($totalProducts == 1 ? ' product' : ' products') . ' imported successfully',
                'errors' => $errors
            ];

        } catch (Exception $e) {

            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    /**
     * Parse boolean values.
     *
     * @param mixed $value
     * @return bool
     */
    private function parseTruthy($value): bool
    {
        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['true', 't', '1', 'yes', 'y'])) {
                return true;
            }
            if (in_array($value, ['false', 'f', '0', 'no', 'n'])) {
                return false;
            }
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Update product visibility.
     *
     * @param array $data
     * @return array
     */
    public function updateProductVisibility(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);
        $products = $store->products()->get();
        $productIdsAndVisibility = $data['visibility'];

        $existingProductIdsAndVisibility = $products->map(function ($product) {
            return ['id' => $product->id, 'visible' => $product->visible];
        });

        $newProductIdsAndVisibility = collect($productIdsAndVisibility)->filter(function ($item) use ($existingProductIdsAndVisibility) {
            return $existingProductIdsAndVisibility->contains('id', $item['id']);
        })->toArray();

        $oldProductIdsAndVisibility = collect($existingProductIdsAndVisibility)->filter(function ($item) use ($productIdsAndVisibility) {
            return collect($productIdsAndVisibility)->doesntContain('id', $item['id']);
        })->toArray();

        $finalProductIdsAndVisibility = array_merge($newProductIdsAndVisibility, $oldProductIdsAndVisibility);
        $finalProductIdsAndVisibility = collect($finalProductIdsAndVisibility)->mapWithKeys(fn($item) => [$item['id'] => $item['visible'] ? 1 : 0])->toArray();

        if (count($finalProductIdsAndVisibility)) {
            DB::table('products')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($finalProductIdsAndVisibility))
                ->update(['visible' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $visibility) {
                    return 'WHEN "' . $id . '" THEN ' . $visibility . ' ';
                }, array_keys($finalProductIdsAndVisibility), $finalProductIdsAndVisibility)) . 'END')]);

            return ['message' => 'Product visibility has been updated'];
        }

        return ['message' => 'No matching products to update'];
    }

    /**
     * Update product arrangement.
     *
     * @param array $data
     * @return array
     */
    public function updateProductArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);
        $parentProductId = $data['parent_product_id'] ?? null;
        $products = $store->products()->when(!empty($parentProductId), fn($query) => $query->where('parent_product_id', $parentProductId))->orderBy('position', 'asc')->get();

        if (isset($data['sort_by'])) {
            $query = $store->products()->when(!empty($parentProductId), fn($query) => $query->where('parent_product_id', $parentProductId));

            switch ($data['sort_by']) {
                case SortProductBy::BEST_SELLING->value:
                    /**
                     * Best Selling Ranking Algorithm:
                     * -------------------------------
                     *
                     * 1) Rank each product by sales velocity.
                     * 2) Products with unlimited or high stock levels have an advantage.
                     * 3) Do not consider cancelled order products.
                     * 4) Do not consider order products of cancelled orders.
                     *
                     * Note: Clone query because the query instance is modified.
                     */
                    $productWithHighestStockQuantity = (clone $query)->where('stock_quantity_type', StockQuantityType::LIMITED->value)->orderBy('stock_quantity', 'desc')->first();
                    $maxStockQuantity = $productWithHighestStockQuantity && $productWithHighestStockQuantity->stock_quantity > 0
                                        ? $productWithHighestStockQuantity->stock_quantity
                                        : 1;
                    $productIds = $query->select('products.id')
                        ->selectRaw(
                            '(
                                (
                                    SELECT SUM(order_products.quantity)
                                    FROM order_products
                                    INNER JOIN orders ON orders.id = order_products.order_id
                                    WHERE order_products.product_id = products.id
                                    AND order_products.is_cancelled = 0
                                    AND orders.status != "cancelled"
                                ) /
                                GREATEST(
                                    (
                                        SELECT DATEDIFF(MAX(orders.created_at), MIN(orders.created_at))
                                        FROM orders
                                        INNER JOIN order_products ON order_products.order_id = orders.id
                                        WHERE order_products.product_id = products.id
                                        AND order_products.is_cancelled = 0
                                    ),
                                    1
                                )
                            ) *
                            (CASE
                                WHEN products.stock_quantity_type = ?
                                    THEN LEAST(products.stock_quantity / ?, 1)
                                ELSE 1
                            END) as sales_rate',
                            [StockQuantityType::LIMITED->value, $maxStockQuantity]
                        )
                        ->orderByDesc('sales_rate')
                        ->pluck('products.id');
                    break;
                case SortProductBy::MOST_STOCK->value:
                    $productIds = $query->select('id')->where('stock_quantity_type', StockQuantityType::LIMITED->value)->orderBy('stock_quantity', 'desc')->pluck('id');
                    break;
                case SortProductBy::LEAST_STOCK->value:
                    $productIds = $query->select('id')->where('stock_quantity_type', StockQuantityType::LIMITED->value)->orderBy('stock_quantity', 'asc')->pluck('id');
                    break;
                case SortProductBy::MOST_DISCOUNTED->value:
                    $productIds = $query->select('id')->where('on_sale', '1')->orderBy('unit_sale_discount_percentage', 'desc')->pluck('id');
                    break;
                case SortProductBy::MOST_EXPENSIVE->value:
                    $productIds = $query->select('id')->orderBy('unit_price', 'desc')->pluck('id');
                    break;
                case SortProductBy::MOST_AFFORDABLE->value:
                    $productIds = $query->select('id')->orderBy('unit_price', 'asc')->pluck('id');
                    break;
                case SortProductBy::ALPHABETICALLY->value:
                    $productIds = $query->select('id')->orderBy('name', 'asc')->pluck('id');
                    break;
                default:
                    return ['message' => 'Cannot sort using the sort by method provided'];
            }
        } else {
            $productIds = $data['product_ids'];
        }

        $originalProductPositions = $products->pluck('position', 'id');

        $arrangement = collect($productIds)->filter(function ($productId) use ($originalProductPositions) {
            return collect($originalProductPositions)->keys()->contains($productId);
        })->toArray();

        $movedProductPositions = collect($arrangement)->mapWithKeys(function ($productId, $newPosition) use ($originalProductPositions) {
            return [$productId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalProductPositions = $originalProductPositions->except(collect($movedProductPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedProductPositions) {
            return [$id => count($movedProductPositions) + $index + 1];
        })->toArray();

        $productPositions = array_merge($movedProductPositions, $adjustedOriginalProductPositions);

        if (count($productPositions)) {
            DB::table('products')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($productPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($productPositions), $productPositions)) . 'END')]);

            return ['message' => 'Product arrangement has been updated'];
        }

        return ['message' => 'No matching products to update'];
    }

    /**
     * Show product.
     *
     * @param Product $product
     * @return ProductResource
     */
    public function showProduct(Product $product): ProductResource
    {
        return $this->showResource($product);
    }

    /**
     * Update product.
     *
     * @param Product $product
     * @param array $data
     * @return array
     */
    public function updateProduct(Product $product, array $data): array
    {
        $tags = $data['tags'] ?? null;
        $categories = $data['categories'] ?? null;
        unset($data['tags'], $data['categories']);

        $deliveryMethodIds = $data['delivery_method_ids'] ?? null;

        $product->update($data);

        if(!is_null($tags)) {
            $this->createProductTags($product, $tags);
        }

        if(!is_null($categories)) {
            $this->createProductCategories($product, $categories);
        }

        if(!is_null($deliveryMethodIds)) {
            $product->deliveryMethods()->sync($deliveryMethodIds);
        }

        return $this->showUpdatedResource($product);
    }

    /**
     * Delete product.
     *
     * @param Product $product
     * @return array
     * @throws Exception
     */
    public function deleteProduct(Product $product): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($product->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        foreach ($product->variants as $variant) {
            $this->deleteProduct($variant);
        }

        $deleted = $product->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Product deleted' : 'Product delete unsuccessful'
        ];
    }

    /**
     * Show product variants.
     *
     * @param Product $product
     * @return ProductResources|array
     */
    public function showProductVariants(Product $product): ProductResources|array
    {
        $query = Product::where('parent_product_id', $product->id)
            ->with($this->getRequestRelationships())
            ->withCount($this->getRequestCountableRelationships())
            ->when(!request()->has('_sort'), fn($query) => $query->latest());

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create product tags.
     *
     * @param Product $product
     * @param array<string> $tags - UUIDs or name as string
     */
    private function createProductTags(Product $product, array $tags) {

        // Process tags
        $tagIds = [];

        if (!empty($tags)) {

            $existingTags = Tag::where('store_id', $product->store_id)->where('type', TagType::PRODUCT->value)->get();

            // Match tags by UUID or name
            $matchingUuidTags = $existingTags->filter(fn($existingTag) => collect($tags)->contains($existingTag->id));
            $matchingNameTags = $existingTags->filter(fn($existingTag) => collect($tags)->contains($existingTag->name));

            // Non-matching tags (new tags to create)
            $nonMatchingNameTags = collect($tags)->filter(function ($tag) use ($existingTags) {
                return !Str::isUuid($tag) && !$existingTags->contains('name', $tag);
            })->unique()->values();

            // Create new tags
            foreach ($nonMatchingNameTags as $tagName) {
                $newTag = Tag::create([
                    'name' => $tagName,
                    'type' => TagType::PRODUCT->value,
                    'store_id' => $product->store_id
                ]);
                $tagIds[] = $newTag->id;
            }

            // Combine all tag IDs (UUIDs, matching names, new tags)
            $tagIds = array_merge(
                $tagIds,
                $matchingUuidTags->pluck('id')->toArray(),
                $matchingNameTags->pluck('id')->toArray()
            );
        }

        $product->tags()->sync($tagIds);
    }

    /**
     * Create product categories.
     *
     * @param Product $product
     * @param array<string> $categories - UUIDs or name as string
     */
    private function createProductCategories(Product $product, array $categories) {

        // Process categories
        $categoryIds = [];

        if (!empty($categories)) {

            $existingCategories = Category::where('store_id', $product->store_id)->get();

            // Match categories by UUID or name
            $matchingUuidCategories = $existingCategories->filter(fn($existingCategory) => collect($categories)->contains($existingCategory->id));
            $matchingNameCategories = $existingCategories->filter(fn($existingCategory) => collect($categories)->contains($existingCategory->name));

            // Non-matching categories (new categories to create)
            $nonMatchingNameCategories = collect($categories)->filter(function ($category) use ($existingCategories) {
                return !Str::isUuid($category) && !$existingCategories->contains('name', $category);
            })->unique()->values();

            // Create new categories
            foreach ($nonMatchingNameCategories as $categoryName) {
                $newCategory = Category::create([
                    'name' => $categoryName,
                    'store_id' => $product->store_id
                ]);
                $categoryIds[] = $newCategory->id;
            }

            // Combine all category IDs (UUIDs, matching names, new categories)
            $categoryIds = array_merge(
                $categoryIds,
                $matchingUuidCategories->pluck('id')->toArray(),
                $matchingNameCategories->pluck('id')->toArray()
            );
        }

        $product->categories()->sync($categoryIds);
    }
}
