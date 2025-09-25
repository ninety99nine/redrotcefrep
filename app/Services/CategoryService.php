<?php

namespace App\Services;

use Exception;
use App\Models\Store;
use App\Models\Category;
use App\Enums\Association;
use App\Enums\SortCategoryBy;
use App\Enums\UploadFolderName;
use App\Enums\StockQuantityType;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryResources;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryService extends BaseService
{
    /**
     * Show categories.
     *
     * @param array $data
     * @return CategoryResources|BinaryFileResponse|array
     */
    public function showCategories(array $data): CategoryResources|BinaryFileResponse|array
    {
        $type = $data['type'] ?? null;
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = Category::query();
        }else if($association == Association::TEAM_MEMBER) {
            $query = Category::where('store_id', $storeId);
        }else {
            $query = Category::where('store_id', $storeId)->visible();
        }

        if($type == 'parent') {
            $query = $query->whereNull('parent_category_id');
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->orderBy('position'));
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create category.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createCategory(array $data): array
    {
        $storeId = $data['store_id'];
        $productIds = $data['product_ids'] ?? null;

        $category = Category::create($data);

        if (!is_null($productIds)) {
            $syncData = [];
            foreach ($productIds as $index => $productId) {
                $syncData[$productId] = ['position' => $index + 1];
            }

            $category->products()->sync($syncData);
        }

        $this->updateCategoryArrangement([
            'store_id' => $storeId,
            'category_ids' => [$category->id]
        ]);

        // Create category photo if provided
        if (isset($data['photo']) && !empty($data['photo'])) {

            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_type' => 'category',
                'mediable_id' => $category->id,
                'upload_folder_name' => UploadFolderName::CATEGORY_PHOTO->value
            ]);

        }

        return $this->showCreatedResource($category);
    }

    /**
     * Update categories.
     *
     * @param array $data
     * @return array
     */
    public function updateCategories(array $data): array
    {
        $storeId = $data['store_id'];
        $categoryIds = $data['category_ids'];
        $fillableData = array_intersect_key($data, array_flip([
            'visible'
        ]));

        $query = Category::whereIn('id', $categoryIds)->where('store_id', $storeId);

        $query->update($fillableData);
        $totalCategories = $query->count();

        return ['updated' => true, 'message' => $totalCategories . ($totalCategories == 1 ? ' category': ' categories') . ' updated'];
    }

    /**
     * Delete Categories.
     *
     * @param array $categoryIds
     * @return array
     * @throws Exception
     */
    public function deleteCategories(array $categoryIds): array
    {
        $categories = Category::whereIn('id', $categoryIds)->get();

        if ($totalCategories = $categories->count()) {

            foreach ($categories as $category) {

                $this->deleteCategory($category);

            }

            return ['message' => $totalCategories . ($totalCategories == 1 ? ' Category' : ' Categories') . ' deleted'];

        } else {
            throw new Exception('No Categories deleted');
        }
    }

    /**
     * Update category visibility.
     *
     * @param array $data
     * @return array
     */
    public function updateCategoryVisibility(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);
        $categories = $store->categories()->get();
        $categoryIdsAndVisibility = $data['visibility'];

        $existingCategoryIdsAndVisibility = $categories->map(function ($category) {
            return ['id' => $category->id, 'visible' => $category->visible];
        });

        $newCategoryIdsAndVisibility = collect($categoryIdsAndVisibility)->filter(function ($item) use ($existingCategoryIdsAndVisibility) {
            return $existingCategoryIdsAndVisibility->contains('id', $item['id']);
        })->toArray();

        $oldCategoryIdsAndVisibility = collect($existingCategoryIdsAndVisibility)->filter(function ($item) use ($categoryIdsAndVisibility) {
            return collect($categoryIdsAndVisibility)->doesntContain('id', $item['id']);
        })->toArray();

        $finalCategoryIdsAndVisibility = array_merge($newCategoryIdsAndVisibility, $oldCategoryIdsAndVisibility);
        $finalCategoryIdsAndVisibility = collect($finalCategoryIdsAndVisibility)->mapWithKeys(fn($item) => [$item['id'] => $item['visible'] ? 1 : 0])->toArray();

        if (count($finalCategoryIdsAndVisibility)) {
            DB::table('categories')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($finalCategoryIdsAndVisibility))
                ->update(['visible' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $visibility) {
                    return 'WHEN "' . $id . '" THEN ' . $visibility . ' ';
                }, array_keys($finalCategoryIdsAndVisibility), $finalCategoryIdsAndVisibility)) . 'END')]);

            return ['message' => 'Category visibility has been updated'];
        }

        return ['message' => 'No matching categories to update'];
    }

    /**
     * Update category arrangement.
     *
     * @param array $data
     * @return array
     */
    public function updateCategoryArrangement(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);
        $parentCategoryId = $data['parent_category_id'] ?? null;
        $categories = $store->categories()->when(!empty($parentCategoryId), fn($query) => $query->where('parent_category_id', $parentCategoryId))->orderBy('position', 'asc')->get();

        if (isset($data['sort_by'])) {
            $query = $store->categories()->when(!empty($parentCategoryId), fn($query) => $query->where('parent_category_id', $parentCategoryId));

            switch ($data['sort_by']) {
                case SortCategoryBy::BEST_SELLING->value:
                    /**
                     * Best Selling Ranking Algorithm:
                     * -------------------------------
                     *
                     * 1) Rank each category by sales velocity.
                     * 2) Categories with unlimited or high stock levels have an advantage.
                     * 3) Do not consider cancelled order categories.
                     * 4) Do not consider order categories of cancelled orders.
                     *
                     * Note: Clone query because the query instance is modified.
                     */
                    $categoryWithHighestStockQuantity = (clone $query)->where('stock_quantity_type', StockQuantityType::LIMITED->value)->orderBy('stock_quantity', 'desc')->first();
                    $maxStockQuantity = $categoryWithHighestStockQuantity && $categoryWithHighestStockQuantity->stock_quantity > 0
                                        ? $categoryWithHighestStockQuantity->stock_quantity
                                        : 1;
                    $categoryIds = $query->select('categories.id')
                        ->selectRaw(
                            '(
                                (
                                    SELECT SUM(order_categories.quantity)
                                    FROM order_categories
                                    INNER JOIN orders ON orders.id = order_categories.order_id
                                    WHERE order_categories.category_id = categories.id
                                    AND order_categories.is_cancelled = 0
                                    AND orders.status != "cancelled"
                                ) /
                                GREATEST(
                                    (
                                        SELECT DATEDIFF(MAX(orders.created_at), MIN(orders.created_at))
                                        FROM orders
                                        INNER JOIN order_categories ON order_categories.order_id = orders.id
                                        WHERE order_categories.category_id = categories.id
                                        AND order_categories.is_cancelled = 0
                                    ),
                                    1
                                )
                            ) *
                            (CASE
                                WHEN categories.stock_quantity_type = ?
                                    THEN LEAST(categories.stock_quantity / ?, 1)
                                ELSE 1
                            END) as sales_rate',
                            [StockQuantityType::LIMITED->value, $maxStockQuantity]
                        )
                        ->orderByDesc('sales_rate')
                        ->pluck('categories.id');
                    break;
                case SortCategoryBy::MOST_STOCK->value:
                    $categoryIds = $query->select('id')->where('stock_quantity_type', StockQuantityType::LIMITED->value)->orderBy('stock_quantity', 'desc')->pluck('id');
                    break;
                case SortCategoryBy::LEAST_STOCK->value:
                    $categoryIds = $query->select('id')->where('stock_quantity_type', StockQuantityType::LIMITED->value)->orderBy('stock_quantity', 'asc')->pluck('id');
                    break;
                case SortCategoryBy::MOST_DISCOUNTED->value:
                    $categoryIds = $query->select('id')->where('on_sale', '1')->orderBy('unit_sale_discount_percentage', 'desc')->pluck('id');
                    break;
                case SortCategoryBy::MOST_EXPENSIVE->value:
                    $categoryIds = $query->select('id')->orderBy('unit_price', 'desc')->pluck('id');
                    break;
                case SortCategoryBy::MOST_AFFORDABLE->value:
                    $categoryIds = $query->select('id')->orderBy('unit_price', 'asc')->pluck('id');
                    break;
                case SortCategoryBy::ALPHABETICALLY->value:
                    $categoryIds = $query->select('id')->orderBy('name', 'asc')->pluck('id');
                    break;
                default:
                    return ['message' => 'Cannot sort using the sort by method provided'];
            }
        } else {
            $categoryIds = $data['category_ids'];
        }

        $originalCategoryPositions = $categories->pluck('position', 'id');

        $arrangement = collect($categoryIds)->filter(function ($categoryId) use ($originalCategoryPositions) {
            return collect($originalCategoryPositions)->keys()->contains($categoryId);
        })->toArray();

        $movedCategoryPositions = collect($arrangement)->mapWithKeys(function ($categoryId, $newPosition) use ($originalCategoryPositions) {
            return [$categoryId => ($newPosition + 1)];
        })->toArray();

        $adjustedOriginalCategoryPositions = $originalCategoryPositions->except(collect($movedCategoryPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedCategoryPositions) {
            return [$id => count($movedCategoryPositions) + $index + 1];
        })->toArray();

        $categoryPositions = array_merge($movedCategoryPositions, $adjustedOriginalCategoryPositions);

        if (count($categoryPositions)) {
            DB::table('categories')
                ->where('store_id', $store->id)
                ->whereIn('id', array_keys($categoryPositions))
                ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                    return 'WHEN "' . $id . '" THEN ' . $position . ' ';
                }, array_keys($categoryPositions), $categoryPositions)) . 'END')]);

            return ['message' => 'Category arrangement has been updated'];
        }

        return ['message' => 'No matching categories to update'];
    }

    /**
     * Show category.
     *
     * @param Category $category
     * @return CategoryResource
     */
    public function showCategory(Category $category): CategoryResource
    {
        return $this->showResource($category);
    }

    /**
     * Update category.
     *
     * @param Category $category
     * @param array $data
     * @return array
     */
    public function updateCategory(Category $category, array $data): array
    {
        $productIds = $data['product_ids'] ?? null;

        $category->update($data);

        if (!is_null($productIds)) {
            $syncData = [];
            foreach ($productIds as $index => $productId) {
                $syncData[$productId] = ['position' => $index + 1];
            }

            $category->products()->sync($syncData);
        }

        return $this->showUpdatedResource($category);
    }

    /**
     * Delete category.
     *
     * @param Category $category
     * @return array
     * @throws Exception
     */
    public function deleteCategory(Category $category): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($category->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $category->delete();

        if ($deleted) {
            return ['message' => 'Category deleted'];
        } else {
            throw new Exception('Category delete unsuccessful');
        }
    }
}
