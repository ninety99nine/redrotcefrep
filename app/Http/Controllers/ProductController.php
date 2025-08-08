<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResources;
use App\Http\Requests\Product\ShowProductRequest;
use App\Http\Requests\Product\ShowProductsRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\DeleteProductsRequest;
use App\Http\Requests\Product\UpdateProductsRequest;
use App\Http\Requests\Product\ImportProductsRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\Product\DownloadProductsRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Http\Requests\Product\showProductVariantsRequest;
use App\Http\Requests\Product\UpdateProductVisibilityRequest;
use App\Http\Requests\Product\UpdateProductArrangementRequest;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    protected $service;

    /**
     * ProductController constructor.
     *
     * @param ProductService $service
     */
    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /**
     * Show products.
     *
     * @param ShowProductsRequest $request
     * @return ProductResources|BinaryFileResponse|array
     */
    public function showProducts(ShowProductsRequest $request): ProductResources|BinaryFileResponse|array
    {
        return $this->service->showProducts($request->validated());
    }

    /**
     * Create product.
     *
     * @param CreateProductRequest $request
     * @return array
     */
    public function createProduct(CreateProductRequest $request): array
    {
        return $this->service->createProduct($request->validated());
    }

    /**
     * Update products.
     *
     * @param UpdateProductsRequest $request
     * @return array
     */
    public function updateProducts(UpdateProductsRequest $request): array
    {
        return $this->service->updateProducts($request->validated());
    }

    /**
     * Delete multiple products.
     *
     * @param DeleteProductsRequest $request
     * @return array
     */
    public function deleteProducts(DeleteProductsRequest $request): array
    {
        $productIds = request()->input('product_ids', []);
        return $this->service->deleteProducts($productIds);
    }

    /**
     * Import products from CSV.
     *
     * @param ImportProductsRequest $request
     * @return array
     */
    public function importProducts(ImportProductsRequest $request): array
    {
        return $this->service->importProducts($request->validated());
    }

    /**
     * Download products.
     *
     * @param DownloadProductsRequest $request
     * @return StreamedResponse
     */
    public function downloadProducts(DownloadProductsRequest $request): StreamedResponse
    {
        return $this->service->downloadProducts($request->validated());
    }

    /**
     * Update product visibility.
     *
     * @param UpdateProductVisibilityRequest $request
     * @return array
     */
    public function updateProductVisibility(UpdateProductVisibilityRequest $request): array
    {
        return $this->service->updateProductVisibility($request->validated());
    }

    /**
     * Update product arrangement.
     *
     * @param UpdateProductArrangementRequest $request
     * @return array
     */
    public function updateProductArrangement(UpdateProductArrangementRequest $request): array
    {
        return $this->service->updateProductArrangement($request->validated());
    }

    /**
     * Show product.
     *
     * @param ShowProductRequest $request
     * @param Product $product
     * @return ProductResource
     */
    public function showProduct(ShowProductRequest $request, Product $product): ProductResource
    {
        return $this->service->showProduct($product);
    }

    /**
     * Update product.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return array
     */
    public function updateProduct(UpdateProductRequest $request, Product $product): array
    {
        return $this->service->updateProduct($product, $request->validated());
    }

    /**
     * Delete product.
     *
     * @param DeleteProductRequest $request
     * @param Product $product
     * @return array
     */
    public function deleteProduct(DeleteProductRequest $request, Product $product): array
    {
        return $this->service->deleteProduct($product);
    }

    /**
     * Show product variants.
     *
     * @param showProductVariantsRequest $request
     * @param Product $product
     * @return ProductResources|array
     */
    public function showProductVariants(showProductVariantsRequest $request, Product $product): ProductResources|array
    {
        return $this->service->showProductVariants($product);
    }
}
