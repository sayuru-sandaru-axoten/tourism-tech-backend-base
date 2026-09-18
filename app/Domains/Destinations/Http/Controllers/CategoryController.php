<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\CreateCategory;
use App\Domains\Destinations\Actions\DeleteCategory;
use App\Domains\Destinations\Actions\UpdateCategory;
use App\Domains\Destinations\Http\Requests\StoreCategoryRequest;
use App\Domains\Destinations\Http\Requests\UpdateCategoryRequest;
use App\Domains\Destinations\Http\Resources\CategoryResource;
use App\Domains\Destinations\Models\Category;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->string('applies_to')->toString(), fn ($query, $appliesTo) => $query->appliesTo($appliesTo))
            ->orderBy('name')
            ->get();

        return ApiResponse::success(CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action): JsonResponse
    {
        $category = $action->handle($request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Category created.', 201);
    }

    public function show(Category $category): JsonResponse
    {
        return ApiResponse::success(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategory $action): JsonResponse
    {
        $category = $action->handle($category, $request->validated());

        return ApiResponse::success(new CategoryResource($category));
    }

    public function destroy(Category $category, DeleteCategory $action): JsonResponse
    {
        $action->handle($category);

        return ApiResponse::success(message: 'Category deleted.');
    }
}
