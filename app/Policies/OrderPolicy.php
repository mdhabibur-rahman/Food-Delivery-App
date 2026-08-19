<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $order->restaurant->user_id === $user->id;
    }

    /**
     * Customer can cancel order only when
     * status is pending or confirmed.
     */
    // public function cancel(User $user, Order $order): bool
    // {
    //     return $order->user_id === $user->id
    //         && in_array($order->status, [
    //             'pending',
    //             'confirmed',
    //         ], true);
    // }
    public function cancel(User $user, Order $order): Response
    {
        if ($order->user_id !== $user->id) {
            return Response::deny(
                'You are not authorized to cancel this order.'
            );
        }

        if (! in_array($order->status, [
            'pending',
            'confirmed',
        ], true)) {
            return Response::deny(
                "You cannot cancel an order with status '{$order->status}'."
            );
        }

        return Response::allow();
    }

    /**
     * Restaurant owner can cancel order only when
     * status is pending or confirmed.
     */
    // public function cancelByRestaurant(User $user, Order $order): bool
    // {
    //     return $order->restaurant->user_id === $user->id
    //         && in_array($order->status, [
    //             'pending',
    //             'confirmed',
    //         ], true);
    // }
    public function cancelByRestaurant(
        User $user,
        Order $order
    ): Response {
        if ($order->restaurant->user_id !== $user->id) {
            return Response::deny(
                'You are not authorized to cancel this order.'
            );
        }

        if (! in_array($order->status, [
            'pending',
            'confirmed',
        ], true)) {
            return Response::deny(
                "You cannot cancel an order with status '{$order->status}'."
            );
        }

        return Response::allow();
    }
}
