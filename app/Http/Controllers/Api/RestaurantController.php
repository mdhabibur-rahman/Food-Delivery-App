<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{

    public function index(): JsonResponse
    {
        $restaurants = Restaurant::query()
            ->with('owner')
            ->latest()
            ->paginate(10);

        return response()->json([
            'restaurants' => $restaurants,
        ]);
    }

    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('restaurants', 'public');
        }

        $restaurant = Restaurant::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);
        // dd([
        //     'content_type' => $request->header('Content-Type'),
        //     'has_file' => $request->hasFile('image'),
        //     'file' => $request->file('image'),
        //     'all_files' => $request->allFiles(),
        //     'all' => $request->all(),
        // ]);

        return response()->json([
            'message' => 'Restaurant created successfully.',
            'restaurant' => $restaurant,
        ], 201);
    }

    public function show(Restaurant $restaurant): JsonResponse
    {
        $restaurant->load('owner');

        return response()->json([
            'restaurant' => $restaurant,
        ]);
    }

    public function update(
        UpdateRestaurantRequest $request,
        Restaurant $restaurant
    ): JsonResponse {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            // Delete old image
            if ($restaurant->image) {
                Storage::disk('public')->delete($restaurant->image);
            }

            // Store new image
            $data['image'] = $request->file('image')
                ->store('restaurants', 'public');
        }

        $restaurant->update($data);

        return response()->json([
            'message' => 'Restaurant updated successfully.',
            'restaurant' => $restaurant->fresh(),
        ]);
    }

    // public function destroy(Restaurant $restaurant): JsonResponse
    // {
    //     Gate::authorize('delete', $restaurant);

    //     $restaurant->delete();

    //     return response()->json([
    //         'message' => 'Restaurant deleted successfully.',
    //     ]);
    // }

    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $this->authorize('delete', $restaurant);

        // Delete restaurant image
        if ($restaurant->image) {
            Storage::disk('public')->delete($restaurant->image);
        }

        // Delete food images
        $restaurant->foods()
            ->whereNotNull('image')
            ->get()
            ->each(function ($food) {
                Storage::disk('public')->delete($food->image);
            });

        // Database cascade will delete:
        // categories + foods
        $restaurant->delete();

        return response()->json([
            'message' => 'Restaurant deleted successfully.',
        ]);
    }
}
