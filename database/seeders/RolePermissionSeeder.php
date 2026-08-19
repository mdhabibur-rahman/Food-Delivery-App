<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // User management
            'view users',
            'create users',
            'update users',
            'delete users',

            // Restaurant management
            'view restaurants',
            'create restaurants',
            'update restaurants',
            'delete restaurants',

            // Food management
            'view foods',
            'create foods',
            'update foods',
            'delete foods',

            // Category management
            'view categories',
            'create categories',
            'update categories',
            'delete categories',

            // Order management
            'view orders',
            'create orders',
            'update orders',
            'cancel orders',

            // Delivery management
            'view deliveries',
            'accept deliveries',
            'update delivery status',

            // Review management
            'view reviews',
            'create reviews',
            'delete reviews',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $customer = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        $restaurantOwner = Role::firstOrCreate([
            'name' => 'restaurant_owner',
            'guard_name' => 'web',
        ]);

        $deliveryRider = Role::firstOrCreate([
            'name' => 'delivery_rider',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign permissions to roles
        |--------------------------------------------------------------------------
        */

        // Admin gets every permission.
        $admin->syncPermissions(Permission::all());

        // Customer permissions.
        $customer->syncPermissions([
            'view restaurants',
            'view foods',
            'view categories',
            'view orders',
            'create orders',
            'cancel orders',
            'create reviews',
            'view reviews',
        ]);

        // Restaurant owner permissions.
        $restaurantOwner->syncPermissions([
            'view restaurants',
            'create restaurants',
            'update restaurants',
            'delete restaurants',

            'view foods',
            'create foods',
            'update foods',
            'delete foods',

            'view categories',
            'create categories',
            'update categories',
            'delete categories',

            'view orders',
            'update orders',

            'view reviews',
        ]);

        // Delivery rider permissions.
        $deliveryRider->syncPermissions([
            'view deliveries',
            'accept deliveries',
            'update delivery status',
        ]);
    }
}
