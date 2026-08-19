<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //show cart
    public function show(Request $request): JsonResponse
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $cart->load([
            'items.food.category',
            'items.food.restaurant',
        ]);

        return response()->json([
            'cart' => $cart,
        ]);
    }

    //add cart item in cart
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'food_id' => [
                'required',
                'integer',
                'exists:foods,id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $food = Food::findOrFail($validated['food_id']);

        // Food availability check
        if (! $food->is_available) {
            return response()->json([
                'message' => 'This food is currently unavailable.',
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Same Restaurant Check
    |--------------------------------------------------------------------------
    */

        $existingRestaurantId = $cart->items()
            ->with('food')
            ->first()?->food?->restaurant_id;

        if (
            $existingRestaurantId !== null
            && $existingRestaurantId !== $food->restaurant_id
        ) {
            return response()->json([
                'message' => 'Your cart contains food from another restaurant.',
                'current_restaurant_id' => $existingRestaurantId,
                'requested_restaurant_id' => $food->restaurant_id,
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Add / Increase Cart Item
    |--------------------------------------------------------------------------
    */

        $cartItem = $cart->items()->firstOrNew([
            'food_id' => $food->id,
        ]);

        $cartItem->quantity = ($cartItem->quantity ?? 0)
            + $validated['quantity'];

        $cartItem->save();

        $cart->load([
            'items.food.category',
            'items.food.restaurant',
        ]);

        return response()->json([
            'message' => 'Food added to cart successfully.',
            'cart' => $cart,
        ], 201);
    }


    //Update cart item quantity.
    public function updateItem(
        Request $request,
        CartItem $cartItem
    ): JsonResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->firstOrFail();

        // Make sure this cart item belongs to current user's cart.
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'message' => 'This cart item does not belong to your cart.',
            ], 403);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
        ]);

        $cart->load([
            'items.food.category',
            'items.food.restaurant',
        ]);

        return response()->json([
            'message' => 'Cart item quantity updated successfully.',
            'cart' => $cart,
        ]);
    }


    //Remove one item from cart.
    public function removeItem(
        Request $request,
        CartItem $cartItem
    ): JsonResponse {
        $cart = Cart::where('user_id', $request->user()->id)
            ->firstOrFail();

        // Make sure this cart item belongs to current user's cart.
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'message' => 'This cart item does not belong to your cart.',
            ], 403);
        }

        $cartItem->delete();

        $cart->load([
            'items.food.category',
            'items.food.restaurant',
        ]);

        return response()->json([
            'message' => 'Food removed from cart successfully.',
            'cart' => $cart,
        ]);
    }


    // Clear entire cart.
    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->firstOrFail();

        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart cleared successfully.',
            'cart' => $cart->load([
                'items.food.category',
                'items.food.restaurant',
            ]),
        ]);
    }
}
