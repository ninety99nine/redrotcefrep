<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryResources;
use App\Http\Requests\Category\ShowCategoryRequest;
use App\Http\Requests\Category\ShowCategoriesRequest;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\Category\DeleteCategoryRequest;
use App\Http\Requests\Category\DeleteCategoriesRequest;
use App\Http\Requests\Category\UpdateCategoriesRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Http\Requests\Category\UpdateCategoryVisibilityRequest;
use App\Http\Requests\Category\UpdateCategoryArrangementRequest;

class CategoryController extends Controller
{
    /**
     * @var CategoryService
     */
    protected $service;

    /**
     * CategoryController constructor.
     *
     * @param CategoryService $service
     */
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Show categories.
     *
     * @param ShowCategoriesRequest $request
     * @return CategoryResources|BinaryFileResponse|array
     */
    public function showCategories(ShowCategoriesRequest $request): CategoryResources|BinaryFileResponse|array
    {
        return $this->service->showCategories($request->validated());
    }

    /**
     * Create category.
     *
     * @param CreateCategoryRequest $request
     * @return array
     */
    public function createCategory(CreateCategoryRequest $request): array
    {
        return $this->service->createCategory($request->validated());
    }

    /**
     * Update categories.
     *
     * @param UpdateCategoriesRequest $request
     * @return array
     */
    public function updateCategories(UpdateCategoriesRequest $request): array
    {
        return $this->service->updateCategories($request->validated());
    }

    /**
     * Delete multiple categories.
     *
     * @param DeleteCategoriesRequest $request
     * @return array
     */
    public function deleteCategories(DeleteCategoriesRequest $request): array
    {
        $categoryIds = request()->input('category_ids', []);
        return $this->service->deleteCategories($categoryIds);
    }

    /**
     * Update category visibility.
     *
     * @param UpdateCategoryVisibilityRequest $request
     * @return array
     */
    public function updateCategoryVisibility(UpdateCategoryVisibilityRequest $request): array
    {
        return $this->service->updateCategoryVisibility($request->validated());
    }

    /**
     * Update category arrangement.
     *
     * @param UpdateCategoryArrangementRequest $request
     * @return array
     */
    public function updateCategoryArrangement(UpdateCategoryArrangementRequest $request): array
    {
        return $this->service->updateCategoryArrangement($request->validated());
    }

    /**
     * Show category.
     *
     * @param ShowCategoryRequest $request
     * @param Category $category
     * @return CategoryResource
     */
    public function showCategory(ShowCategoryRequest $request, Category $category): CategoryResource
    {
        return $this->service->showCategory($category);
    }

    /**
     * Update category.
     *
     * @param UpdateCategoryRequest $request
     * @param Category $category
     * @return array
     */
    public function updateCategory(UpdateCategoryRequest $request, Category $category): array
    {
        return $this->service->updateCategory($category, $request->validated());
    }

    /**
     * Delete category.
     *
     * @param DeleteCategoryRequest $request
     * @param Category $category
     * @return array
     */
    public function deleteCategory(DeleteCategoryRequest $request, Category $category): array
    {
        return $this->service->deleteCategory($category);
    }
}
