<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage users',
            'manage categories',
            'manage products',
            'manage stores',
            'manage auctions',
            'manage orders',
            'manage reviews',
            'place bids',
            'create orders',
            'view own orders',
            'cancel own orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $artisanRole = Role::firstOrCreate(['name' => 'artisan']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $artisanRole->givePermissionTo([
            'manage products',
            'manage stores',
            'manage auctions',
            'place bids',
            'create orders',
        ]);

        $buyerRole->givePermissionTo([
            'place bids',
            'create orders',
            'view own orders',
            'cancel own orders',
            'manage reviews',
        ]);
    }
}
