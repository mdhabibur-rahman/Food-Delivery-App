<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

class CategoryController extends Controller
{


    public function index(Restaurant $restaurant)
    {
        $categories = $restaurant->categories()
            ->latest()
            ->paginate(10);

        // return response()->json([
        //     'categories' => $categories,
        // ]);
        return CategoryResource::collection($categories);
    }

    public function store(
        StoreCategoryRequest $request,
        Restaurant $restaurant
    ): JsonResponse {
        $category = $restaurant->categories()->create(
            $request->validated()
        );

        // return response()->json([
        //     'message' => 'Category created successfully.',
        //     'category' => $category,
        // ], 201);
        return response()->json([
            'message' => 'Category created successfully.',
            'category' => CategoryResource::make($category),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        // $this->authorize('view', $category);
        // return response()->json([
        //     'category' => $category,
        // ]);
        return response()->json([
            'category' => CategoryResource::make($category),
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse {
        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => CategoryResource::make(
                $category->fresh()
            ),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
