<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $admin = User::updateOrCreate(
            ['email' => 'admin@fooddelivery.test'],
            [
                'name' => 'Admin User',
                'password' => 'password',
            ]
        );

        $admin->syncRoles(['admin']);

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        $customer = User::updateOrCreate(
            ['email' => 'customer@fooddelivery.test'],
            [
                'name' => 'Customer User',
                'password' => 'password',
            ]
        );

        $customer->syncRoles(['customer']);

        /*
        |--------------------------------------------------------------------------
        | Restaurant Owner
        |--------------------------------------------------------------------------
        */

        $restaurantOwner = User::updateOrCreate(
            ['email' => 'restaurant@fooddelivery.test'],
            [
                'name' => 'Restaurant Owner',
                'password' => 'password',
            ]
        );

        $restaurantOwner->syncRoles(['restaurant_owner']);

        /*
        |--------------------------------------------------------------------------
        | Delivery Rider
        |--------------------------------------------------------------------------
        */

        $deliveryRider = User::updateOrCreate(
            ['email' => 'rider@fooddelivery.test'],
            [
                'name' => 'Delivery Rider',
                'password' => 'password',
            ]
        );

        $deliveryRider->syncRoles(['delivery_rider']);
    }
}
