<?php

namespace App\Services;

use App\Enums\Association;
use Exception;
use App\Models\Store;
use App\Models\Product;
use App\Models\Variable;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Enums\SortProductBy;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\UploadFolderName;
use App\Enums\StockQuantityType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResources;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductService extends BaseService
{
    const MAXIMUM_VARIATIONS_PER_PRODUCT = 100;

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
            $query = Product::query()->doesNotSupportVariations();
        }else if($association == Association::TEAM_MEMBER) {
            $query = Product::where('store_id', $storeId)->doesNotSupportVariations();
        }else {
            $query = Product::where('store_id', $storeId)->doesNotSupportVariations()->visible();
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
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
        $store = Store::find($storeId);

        $data = array_merge($data, [
            'currency' => $store->currency
        ]);

        $product = Product::create($data);

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
        $totalProducts = count($productIds);
        $fillableData = array_intersect_key($data, array_flip([
            'visible'
        ]));

        Product::whereIn('id', $productIds)->where('store_id', $storeId)->update($fillableData);
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
        $products = $store->products()->orderBy('position', 'asc')->get();

        if (isset($data['sort_by'])) {
            $query = $store->products();

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
        $product->update($data);
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

        foreach ($product->variations as $variation) {
            $this->deleteProduct($variation);
        }

        $deleted = $product->delete();

        if ($deleted) {
            return ['message' => 'Product deleted'];
        } else {
            throw new Exception('Product delete unsuccessful');
        }
    }

    /**
     * Show product variations.
     *
     * @param Product $product
     * @return ProductResources|array
     */
    public function showProductVariations(Product $product): ProductResources|array
    {
        $query = Product::where('parent_product_id', $product->id)
            ->with($this->getRequestRelationships())
            ->withCount($this->getRequestCountableRelationships())
            ->when(!request()->has('_sort'), fn($query) => $query->latest());

        return $this->setQuery($query)->getOutput();
    }

    /**
     *  Create product variations.
     *
     * @param Product $product
     * @param array $data
     * @return ProductResources|array
     */
    public function createProductVariations(Product $product, array $data): ProductResources|array
    {
        $variantAttributes = $data['variant_attributes'];
        $variantAttributes = $this->normalizeVariantAttributes($variantAttributes);
        $variantAttributeMatrix = $this->generateVariantAttributeMatrix($variantAttributes);
        $productVariationTemplates = $this->generateProductVariationTemplates($product, $variantAttributes, $variantAttributeMatrix);
        [$matchedProductVariations, $unMatchedProductVariations] = $this->matchProductVariations($product, $productVariationTemplates);
        $totalProductVariations = $matchedProductVariations->count() + $unMatchedProductVariations->count();

        if($totalProductVariations > self::MAXIMUM_VARIATIONS_PER_PRODUCT) {
            return ['message' => 'This product has '.$totalProductVariations.' variations which is greater than the maximum limit of '.self::MAXIMUM_VARIATIONS_PER_PRODUCT.' variations per product'];
        }

        if ($unMatchedProductVariations->count()) {
            $this->deleteUnmatchedProductVariations($unMatchedProductVariations);
        }

        if ($productVariationTemplates->count()) {
            $this->createNewProductVariations($productVariationTemplates, $product, $variantAttributes);
        }

        return $this->setQuery($product->variations())->getOutput();
    }

    /**
     * Normalize variant attributes.
     *
     * @param array $variantAttributes
     * @return Collection
     */
    private function normalizeVariantAttributes(array $variantAttributes): Collection
    {
        return collect($variantAttributes)->map(function ($variantAttribute) {
            $variantAttribute['name'] = ucfirst($variantAttribute['name']);
            if (!isset($variantAttribute['instruction']) || empty($variantAttribute['instruction'])) {
                $variantAttribute['instruction'] = 'Select option';
            }
            return $variantAttribute;
        });
    }

    /**
     * Generate variant attribute matrix.
     *
     * @param Collection $variantAttributes
     * @return array
     */
    private function generateVariantAttributeMatrix(Collection $variantAttributes): array
    {
        $variantAttributesRestructured = $variantAttributes->mapWithKeys(function ($variantAttribute) {
            return [$variantAttribute['name'] => $variantAttribute['values']];
        });

        return Arr::crossJoin(...$variantAttributesRestructured->values());
    }

    /**
     * Generate product variation templates.
     *
     * @param Product $product
     * @param Collection $variantAttributes
     * @param array $variantAttributeMatrix
     * @return Collection
     */
    private function generateProductVariationTemplates(Product $product, Collection $variantAttributes, array $variantAttributeMatrix): Collection
    {
        return collect($variantAttributeMatrix)->map(function ($options) use ($product, $variantAttributes) {
            $name = $product->name . ' (' . trim(collect($options)->map(fn($option) => ucfirst($option))->join(', ')) . ')';

            $template = [
                'id' => Str::uuid(),
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
                'user_id' => auth()->user()->id,
                'store_id' => $product->store_id,
                'parent_product_id' => $product->id,
                'variable_templates' => collect($options)->map(function ($option, $key) use ($variantAttributes) {
                    $variantAttributeNames = $variantAttributes->keys();
                    return [
                        'id' => Str::uuid(),
                        'value' => $option,
                        'name' => $variantAttributeNames->get($key),
                    ];
                })
            ];

            return $template;
        });
    }

    /**
     * Match product variations.
     *
     * @param Product $product
     * @param Collection $productVariationTemplates
     * @return Collection
     */
    private function matchProductVariations(Product $product, Collection &$productVariationTemplates): Collection
    {
        $existingProductVariations = $product->variations()->with('variables')->get();

        return $existingProductVariations->partition(function ($existingProductVariation) use (&$productVariationTemplates) {
            $result1 = $existingProductVariation->variables->mapWithKeys(function ($variable) {
                return [$variable->name => $variable->value];
            });

            return collect($productVariationTemplates)->contains(function ($productVariationTemplate, $key) use ($result1, &$productVariationTemplates) {
                $result2 = collect($productVariationTemplate['variable_templates'])->mapWithKeys(function ($variable) {
                    return [$variable['name'] => $variable['value']];
                });

                $exists = $result1->diffAssoc($result2)->isEmpty() && $result2->diffAssoc($result1)->isEmpty();

                if ($exists) {
                    $productVariationTemplates->forget($key);
                }

                return $exists;
            });
        });
    }

    /**
     * Delete unmatched product variations.
     *
     * @param Collection $unMatchedProductVariations
     */
    private function deleteUnmatchedProductVariations(Collection $unMatchedProductVariations): void
    {
        $unMatchedProductVariations->each(fn($unMatchedProductVariation) => $unMatchedProductVariation->delete());
    }

    /**
     * Create new product variations.
     *
     * @param Collection $productVariationTemplates
     * @param Product $product
     * @param Collection $variantAttributes
     */
    private function createNewProductVariations(Collection $productVariationTemplates, Product $product, Collection $variantAttributes): void
    {
        Product::insert(
            $productVariationTemplates->map(
                fn($productVariationTemplate) => collect($productVariationTemplate)->only(['id', 'name', 'parent_product_id', 'user_id', 'store_id', 'created_at', 'updated_at'])
            )->toArray()
        );

        $existingProductVariations = $product->variations()->get();
        $variableTemplates = $this->generateVariableTemplates($existingProductVariations, $productVariationTemplates);

        Variable::insert($variableTemplates->toArray());
        $totalVariations = $product->variations()->count();
        $totalVisibleVariations = $product->variations()->visible()->count();

        $product->update([
            'allow_variations' => true,
            'total_variations' => $totalVariations,
            'variant_attributes' => $variantAttributes,
            'total_visible_variations' => $totalVisibleVariations
        ]);
    }

    /**
     * Generate variable templates.
     *
     * @param Collection $existingProductVariations
     * @param Collection $productVariationTemplates
     * @return Collection
     */
    private function generateVariableTemplates(Collection $existingProductVariations, Collection $productVariationTemplates): Collection
    {
        return $existingProductVariations->flatMap(function ($existingProductVariation) use ($productVariationTemplates) {

            $productVariationTemplate = $productVariationTemplates->first(fn($productVariationTemplate) => $existingProductVariation->name === $productVariationTemplate['name']);

            if ($productVariationTemplate) {
                $variableTemplates = $productVariationTemplate['variable_templates'];

                return collect($variableTemplates)->map(function ($variableTemplate) use ($existingProductVariation) {
                    $variableTemplate['product_id'] = $existingProductVariation->id;
                    return $variableTemplate;
                });
            }

            return [];
        });
    }
}
