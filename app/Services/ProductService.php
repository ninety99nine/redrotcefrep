<?php

namespace App\Services;

use Exception;
use App\Models\Tag;
use App\Models\Store;
use League\Csv\Reader;
use App\Models\Product;
use App\Models\Category;
use App\Enums\Association;
use App\Enums\ProductType;
use App\Enums\ProductUnitType;
use Illuminate\Support\Str;
use App\Enums\SortProductBy;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\UploadFolderName;
use App\Enums\StockQuantityType;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResources;
use App\Http\Requests\Product\CreateProductRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = Product::isNotVariant();
        }else if($association == Association::TEAM_MEMBER) {
            $query = Product::isNotVariant()->where('store_id', $storeId);
        }else {
            $query = Product::isNotVariant()->where('store_id', $storeId)->visible();
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
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
            if (!isset($productData['id'])) {
                continue;
            }

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

            // Handle tags
            if (!is_null($tagsToAdd)) {
                $product->tags()->syncWithoutDetaching($tagsToAdd);
            }

            if (!is_null($tagsToRemove)) {
                $product->tags()->detach($tagsToRemove);
            }

            // Handle categories
            if (!is_null($categoriesToAdd)) {
                $product->categories()->syncWithoutDetaching($categoriesToAdd);
            }

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

                    $name = $record['Name'] ?? null;

                    if (!$name) {
                        $errors[] = "Row " . ($index + 2) . ": Name is required.";
                        continue;
                    }

                    $productId = ($record['ID'] && Str::isUuid($record['ID'])) ? $record['ID'] : Str::uuid()->toString();
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
                        'store_id' => $storeId,
                        'currency' => $store->currency,
                        'id' => $productId,
                        'name' => $name,
                        'parent_product_id' => $parentProductId,
                        'is_free' => $this->parseBoolean($record['Free'] ?? false),
                        'is_estimated_price' => $this->parseBoolean($record['Estimated Price'] ?? false),
                        'unit_regular_price' => $record['Regular Price'] ? floatval($record['Regular Price']) : '0.00',
                        'unit_sale_price' => $record['Sale Price'] ? floatval($record['Sale Price']) : '0.00',
                        'unit_cost_price' => $record['Cost Price'] ? floatval($record['Cost Price']) : '0.00',
                        'visible' => $this->parseBoolean($record['Visible'] ?? true),
                        'type' => $record['Type'] ?? ProductType::PHYSICAL->value,
                        'download_link' => $record['Download Link'] ?? null,
                        'sku' => $record['Sku'] ?? null,
                        'barcode' => $record['Barcode'] ?? null,
                        'show_description' => $this->parseBoolean($record['Show Description'] ?? false),
                        'description' => $record['Description'] ?? null,
                        'unit_weight' => $record['Weight'] ? floatval($record['Weight']) : '0.00',
                        'tax_overide' => $this->parseBoolean($record['Tax Override'] ?? false),
                        'tax_overide_amount' => $record['Tax Override Amount'] ? floatval($record['Tax Override Amount']) : '0.00',
                        'show_price_per_unit' => $this->parseBoolean($record['Show Price Per Unit'] ?? false),
                        'unit_value' => $record['Unit Value'] ? floatval($record['Unit Value']) : '1',
                        'unit_type' => $record['Unit Type'] ?? ProductUnitType::QUANTITY->value,
                        'set_daily_capacity' => $this->parseBoolean($record['Set Daily Capacity'] ?? false),
                        'daily_capacity' => $record['Daily Capacity'] ? intval($record['Daily Capacity']) : '1',
                        'stock_quantity_type' => $record['Stock Type'] ?? StockQuantityType::UNLIMITED->value,
                        'stock_quantity' => $record['Stock Quantity'] ? intval($record['Stock Quantity']) : '100',
                        'set_min_order_quantity' => $this->parseBoolean($record['Set Min Order Quantity'] ?? false),
                        'min_order_quantity' => $record['Min Order Quantity'] ? intval($record['Min Order Quantity']) : '1',
                        'set_max_order_quantity' => $this->parseBoolean($record['Set Max Order Quantity'] ?? false),
                        'max_order_quantity' => $record['Max Order Quantity'] ? intval($record['Max Order Quantity']) : '1',
                        'categories' => $record['Categories'] ? array_map('trim', explode(',', $record['Categories'])) : null,
                        'tags' => $record['Tags'] ? array_map('trim', explode(',', $record['Tags'])) : null,
                        'position' => $record['Position'] ? intval($record['Position']) : null,
                    ];

                    // Validate product data
                    $validator = validator($productData, (new \App\Http\Requests\Product\CreateProductRequest)->rules(), (new \App\Http\Requests\Product\CreateProductRequest)->messages());

                    if ($validator->fails()) {
                        $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    $csvRecordIds[$name] = $record['ID'] ?? null;
                    $productsToCreate[] = $productData;
                    $totalProducts++;

                } catch (Exception $e) {

                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();

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

                    $validator = validator($parentData, (new \App\Http\Requests\Product\CreateProductRequest)->rules(), (new \App\Http\Requests\Product\CreateProductRequest)->messages());

                    if ($validator->fails()) {
                        $errors[] = "Parent product '$parentName': " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    $productsToCreate[] = $parentData;
                    $totalProducts++;

                }
            }

            if (!empty($errors)) {
                throw new Exception('Validation errors occurred: ' . implode('; ', $errors));
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

            return ['message' => $totalProducts . ($totalProducts == 1 ? ' product' : ' products') . ' imported successfully'];

        } catch (Exception $e) {

            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    /**
     * Parse boolean values, including yes/no and y/n.
     *
     * @param mixed $value
     * @return bool
     */
    private function parseBoolean($value): bool
    {
        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['yes', 'y'])) {
                return true;
            }
            if (in_array($value, ['no', 'n'])) {
                return false;
            }
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Download products.
     *
     * @param array $data
     * @return StreamedResponse
     */
    public function downloadProducts(array $data): StreamedResponse
    {
        $storeId = $data['store_id'];
        $productIds = $data['product_ids'];

        $store = Store::with(['logo'])->find($storeId);
        $products = Product::whereIn('id', $productIds)->where('store_id', $storeId)->with(['photos'])->get();

        // Convert objects to arrays
        $products = $products->map(function ($product) {
            return json_decode(json_encode($product), true);
        })->toArray();

        // Generate the PDF
        $pdf = Pdf::loadView('pdfs.product.show', compact('store', 'products'));

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'name.pdf');
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

        if ($deleted) {
            return ['message' => 'Product deleted'];
        } else {
            throw new Exception('Product delete unsuccessful');
        }
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

            $existingTags = Tag::where('store_id', $product->store_id)->get();

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
