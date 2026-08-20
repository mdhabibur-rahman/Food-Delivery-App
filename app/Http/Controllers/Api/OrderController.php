<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with([
            'items.food',
            'restaurant',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    // public function checkout(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'delivery_address' => [
    //             'required',
    //             'string',
    //             'max:1000',
    //         ],
    //     ]);

    //     $user = $request->user();

    //     $cart = Cart::with('items.food')
    //         ->where('user_id', $user->id)
    //         ->firstOrFail();

    //     if ($cart->items->isEmpty()) {
    //         return response()->json([
    //             'message' => 'Your cart is empty.',
    //         ], 422);
    //     }

    //     return DB::transaction(function () use ($cart, $request, $user) {

    //         $restaurantId = $cart->items->first()->food->restaurant_id;

    //         $subtotal = $cart->items->sum(function ($item) {
    //             return $item->quantity * $item->food->price;
    //         });

    //         $deliveryFee = 0;

    //         $total = $subtotal + $deliveryFee;

    //         $order = Order::create([
    //             'user_id' => $user->id,
    //             'restaurant_id' => $restaurantId,
    //             // 'order_number' => 'ORD-' . strtoupper(uniqid()),
    //             'order_number' => 'ORD-' . strtoupper(Str::random(10)),
    //             'status' => 'pending',
    //             'subtotal' => $subtotal,
    //             'delivery_fee' => $deliveryFee,
    //             'total' => $total,
    //             'delivery_address' => $request->delivery_address,
    //         ]);

    //         // পরের ধাপে OrderItems তৈরি করব
    //         foreach ($cart->items as $cartItem) {
    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'food_id' => $cartItem->food_id,
    //                 'quantity' => $cartItem->quantity,
    //                 'unit_price' => $cartItem->food->price,
    //                 'subtotal' => $cartItem->quantity * $cartItem->food->price,
    //             ]);
    //         }

    //         $cart->items()->delete();

    //         $order->load([
    //             'items.food',
    //             'restaurant',
    //         ]);

    //         return response()->json([
    //             'message' => 'Order placed successfully.',
    //             'order' => $order,
    //         ], 201);
    //     });
    // }
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_address' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $user = $request->user();

        $cart = Cart::with('items.food')
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
            ], 422);
        }

        // Make sure all cart items belong to the same restaurant.
        $restaurantIds = $cart->items
            ->pluck('food.restaurant_id')
            ->unique();

        if ($restaurantIds->count() > 1) {
            return response()->json([
                'message' => 'All items in your cart must be from the same restaurant.',
            ], 422);
        }

        // Make sure all foods are currently available.
        $unavailableItems = $cart->items->filter(function ($item) {
            return ! $item->food->is_available;
        });

        if ($unavailableItems->isNotEmpty()) {
            return response()->json([
                'message' => 'Some items in your cart are currently unavailable.',
            ], 422);
        }

        return DB::transaction(function () use ($cart, $request, $user) {

            $restaurantId = $cart->items->first()->food->restaurant_id;

            $subtotal = $cart->items->sum(function ($item) {
                return $item->quantity * $item->food->price;
            });

            $deliveryFee = 0;

            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurantId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'delivery_address' => $request->delivery_address,
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'food_id' => $cartItem->food_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->food->price,
                    'subtotal' => $cartItem->quantity * $cartItem->food->price,
                ]);
            }

            $cart->items()->delete();

            $order->load([
                'items.food',
                'restaurant',
            ]);

            return response()->json([
                'message' => 'Order placed successfully.',
                'order' => $order,
            ], 201);
        });
    }

    public function show(Order $order): JsonResponse
    {
        // if ($order->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'message' => 'You are not authorized to view this order.',
        //     ], 403);
        // }
        $this->authorize('view', $order);

        $order->load([
            'items.food',
            'restaurant',
        ]);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $order->update([
            'status' => 'cancelled',
        ]);

        $order->load([
            'items.food',
            'restaurant',
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => $order,
        ]);
    }

    // restaurant owner action
    public function restaurantOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                'string',
                'in:pending,confirmed,preparing,out_for_delivery,delivered,cancelled',
            ],
        ]);

        $user = $request->user();

        abort_unless(
            $user->hasPermissionTo('view orders'),
            403,
            'You are not authorized to view restaurant orders.'
        );

        $orders = Order::with([
            'items.food',
            'restaurant',
            'user',
        ])
            ->whereHas('restaurant', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when(
                $validated['status'] ?? null,
                function ($query, $status) {
                    $query->where('status', $status);
                }
            )
            ->latest()
            ->paginate(10);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function updateStatus(
        Request $request,
        Order $order
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:confirmed,preparing,out_for_delivery,delivered',
            ],
        ]);

        // $user = $request->user();

        // Make sure this order belongs to a restaurant owned by current user.
        // if ($order->restaurant->user_id !== $user->id) {
        //     return response()->json([
        //         'message' => 'You are not authorized to update this order.',
        //     ], 403);
        // }
        $this->authorize('update', $order);

        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        $allowedTransitions = [
            'pending' => [
                'confirmed',
            ],

            'confirmed' => [
                'preparing',
            ],

            'preparing' => [
                'out_for_delivery',
            ],

            'out_for_delivery' => [
                'delivered',
            ],

            'delivered' => [],

            'cancelled' => [],
        ];

        if (! in_array(
            $newStatus,
            $allowedTransitions[$currentStatus] ?? [],
            true
        )) {
            return response()->json([
                'message' => "Cannot change order status from {$currentStatus} to {$newStatus}.",
            ], 422);
        }

        $order->update([
            'status' => $newStatus,
        ]);

        $order->load([
            'items.food',
            'restaurant',
        ]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order,
        ]);
    }


    public function cancelByRestaurant(Order $order): JsonResponse
    {
        $this->authorize('cancelByRestaurant', $order);

        $order->update([
            'status' => 'cancelled',
        ]);

        $order->load([
            'items.food',
            'restaurant',
        ]);

        return response()->json([
            'message' => 'Order cancelled by restaurant.',
            'order' => $order,
        ]);
    }
}
