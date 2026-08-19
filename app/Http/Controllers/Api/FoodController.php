<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodRequest;
use App\Http\Requests\UpdateFoodRequest;
use App\Http\Resources\FoodResource;
use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        $foods = $restaurant->foods()
            ->with('category')
            ->latest()
            ->paginate(10);

        // return response()->json([
        //     'foods' => $foods,
        // ]);
        return FoodResource::collection($foods);
    }

    public function store(
        StoreFoodRequest $request,
        Restaurant $restaurant
    ): JsonResponse {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store(
                'foods',
                'public'
            );
        }

        $food = $restaurant->foods()->create($data);

        // return response()->json([
        //     'message' => 'Food created successfully.',
        //     'food' => $food,
        // ], 201);
        return response()->json([
            'message' => 'Food created successfully.',
            'food' => new FoodResource($food),
        ], 201);
    }

    public function show(Food $food): JsonResponse
    {
        // $this->authorize('view', $food);

        $food->load('category');

        // return response()->json([
        //     'food' => $food,
        // ]);
        return response()->json([
            'food' => new FoodResource($food),
        ]);
    }

    public function update(
        UpdateFoodRequest $request,
        Food $food
    ): JsonResponse {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            // Delete old image
            if ($food->image) {
                Storage::disk('public')->delete($food->image);
            }

            // Store new image
            $data['image'] = $request->file('image')->store(
                'foods',
                'public'
            );
        }

        $food->update($data);

        // return response()->json([
        //     'message' => 'Food updated successfully.',
        //     'food' => $food->fresh()->load('category'),
        // ]);
        return response()->json([
            'message' => 'Food updated successfully.',
            'food' => new FoodResource(
                $food->fresh()->load('category')
            ),
        ]);
    }

    public function destroy(Food $food): JsonResponse
    {
        $this->authorize('delete', $food);

        if ($food->image) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();

        return response()->json([
            'message' => 'Food deleted successfully.',
        ]);
    }
}
