<?php

namespace App\Services;

use Exception;
use App\Models\Tag;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Enums\Association;
use Illuminate\Support\Str;
use App\Enums\SortProductBy;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\UploadFolderName;
use App\Enums\StockQuantityType;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResources;
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
            $query = Product::query();
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
     * Update products.
     *
     * @param array $data
     * @return array
     */
    public function updateProducts(array $data): array
    {
        $storeId = $data['store_id'];
        $productIds = $data['product_ids'];
        $fillableData = array_intersect_key($data, array_flip([
            'visible'
        ]));

        $query = Product::whereIn('id', $productIds)->where('store_id', $storeId);

        $products = $query->get();
        $query->update($fillableData);
        $totalProducts = count($products);

        if($totalProducts) {

            if (!empty($data['tags_to_add'])) {
                foreach ($products as $product) {
                    $product->tags()->syncWithoutDetaching($data['tags_to_add']);
                }
            }

            if (!empty($data['tags_to_remove'])) {
                foreach ($products as $product) {
                    $product->tags()->detach($data['tags_to_remove']);
                }
            }

            if (!empty($data['categories_to_add'])) {
                foreach ($products as $product) {
                    $product->categories()->syncWithoutDetaching($data['categories_to_add']);
                }
            }

            // Handle categories to remove
            if (!empty($data['categories_to_remove'])) {
                foreach ($products as $product) {
                    $product->categories()->detach($data['categories_to_remove']);
                }
            }

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
