<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Ensure Roles Exist
        |--------------------------------------------------------------------------
        */

        $admin = Role::where('name', 'admin')->firstOrFail();

        $user = Role::where('name', 'user')->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Ensure Admin Permissions Exist
        |--------------------------------------------------------------------------
        |
        | Admin Portal permissions.
        |
        */

        $adminPermissions = [
            // Dashboard
            'dashboard.view',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Leads
            'leads.view',
            'leads.update',
            'leads.delete',

            // Software
            'software.view',
            'software.create',
            'software.update',
            'software.delete',

            // Vendors
            'vendors.view',
            'vendors.create',
            'vendors.update',
            'vendors.delete',

            // Categories
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Features
            'features.view',
            'features.create',
            'features.update',
            'features.delete',

            // Industries
            'industries.view',
            'industries.create',
            'industries.update',
            'industries.delete',

            // Business Sizes
            'business-sizes.view',
            'business-sizes.create',
            'business-sizes.update',
            'business-sizes.delete',

            // Integrations
            'integrations.view',
            'integrations.create',
            'integrations.update',
            'integrations.delete',

            // Partners
            'partners.view',
            'partners.create',
            'partners.update',
            'partners.delete',

            // Articles
            'articles.view',
            'articles.create',
            'articles.update',
            'articles.delete',

            // Reviews
            'reviews.view',
            'reviews.update',
            'reviews.delete',
        ];


        /*
        |--------------------------------------------------------------------------
        | Create Missing Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($adminPermissions as $permissionName) {
            Permission::firstOrCreate(
                [
                    'name' => $permissionName,
                ],
                [
                    'display_name' => ucwords(
                        str_replace(
                            ['.', '-', '_'],
                            ' ',
                            $permissionName
                        )
                    ),
                    'description' => 'Permission for ' . $permissionName,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Give ALL Permissions To Admin
        |--------------------------------------------------------------------------
        */

        $permissions = Permission::all();

        $admin->permissions()->sync(
            $permissions->pluck('id')->toArray()
        );


        /*
        |--------------------------------------------------------------------------
        | User Permissions
        |--------------------------------------------------------------------------
        |
        | Regular users only get permissions required
        | for public/authenticated functionality.
        |
        */

        $userPermissions = Permission::whereIn('name', [
            'software.view',
            'reviews.view',
        ])->pluck('id')->toArray();

        $user->permissions()->sync($userPermissions);
    }
}