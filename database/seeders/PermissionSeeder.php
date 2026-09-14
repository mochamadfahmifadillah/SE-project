<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'dashboard.view',
                'display_name' => 'View Dashboard',
                'description' => 'View admin dashboard.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Software
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'software.view',
                'display_name' => 'View Software',
                'description' => 'View software catalog in admin portal.',
            ],

            [
                'name' => 'software.create',
                'display_name' => 'Create Software',
                'description' => 'Create new software.',
            ],

            [
                'name' => 'software.update',
                'display_name' => 'Update Software',
                'description' => 'Update software information.',
            ],

            [
                'name' => 'software.delete',
                'display_name' => 'Delete Software',
                'description' => 'Delete software.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Vendors
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'vendors.view',
                'display_name' => 'View Vendors',
                'description' => 'View vendors in admin portal.',
            ],

            [
                'name' => 'vendors.create',
                'display_name' => 'Create Vendors',
                'description' => 'Create new vendors.',
            ],

            [
                'name' => 'vendors.update',
                'display_name' => 'Update Vendors',
                'description' => 'Update vendor information.',
            ],

            [
                'name' => 'vendors.delete',
                'display_name' => 'Delete Vendors',
                'description' => 'Delete vendors.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'categories.view',
                'display_name' => 'View Categories',
                'description' => 'View software categories in admin portal.',
            ],

            [
                'name' => 'categories.create',
                'display_name' => 'Create Categories',
                'description' => 'Create new software categories.',
            ],

            [
                'name' => 'categories.update',
                'display_name' => 'Update Categories',
                'description' => 'Update software category information.',
            ],

            [
                'name' => 'categories.delete',
                'display_name' => 'Delete Categories',
                'description' => 'Delete software categories.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'features.view',
                'display_name' => 'View Features',
                'description' => 'View software features in admin portal.',
            ],

            [
                'name' => 'features.create',
                'display_name' => 'Create Features',
                'description' => 'Create new software features.',
            ],

            [
                'name' => 'features.update',
                'display_name' => 'Update Features',
                'description' => 'Update software feature information.',
            ],

            [
                'name' => 'features.delete',
                'display_name' => 'Delete Features',
                'description' => 'Delete software features.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Industries
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'industries.view',
                'display_name' => 'View Industries',
                'description' => 'View software industries in admin portal.',
            ],

            [
                'name' => 'industries.create',
                'display_name' => 'Create Industries',
                'description' => 'Create new software industries.',
            ],

            [
                'name' => 'industries.update',
                'display_name' => 'Update Industries',
                'description' => 'Update software industry information.',
            ],

            [
                'name' => 'industries.delete',
                'display_name' => 'Delete Industries',
                'description' => 'Delete software industries.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Business Sizes
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'business-sizes.view',
                'display_name' => 'View Business Sizes',
                'description' => 'View business size classifications.',
            ],

            [
                'name' => 'business-sizes.create',
                'display_name' => 'Create Business Sizes',
                'description' => 'Create new business size classifications.',
            ],

            [
                'name' => 'business-sizes.update',
                'display_name' => 'Update Business Sizes',
                'description' => 'Update business size information.',
            ],

            [
                'name' => 'business-sizes.delete',
                'display_name' => 'Delete Business Sizes',
                'description' => 'Delete business size classifications.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Integrations
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'integrations.view',
                'display_name' => 'View Integrations',
                'description' => 'View software integrations.',
            ],

            [
                'name' => 'integrations.create',
                'display_name' => 'Create Integrations',
                'description' => 'Create new software integrations.',
            ],

            [
                'name' => 'integrations.update',
                'display_name' => 'Update Integrations',
                'description' => 'Update software integration information.',
            ],

            [
                'name' => 'integrations.delete',
                'display_name' => 'Delete Integrations',
                'description' => 'Delete software integrations.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'users.view',
                'display_name' => 'View Users',
                'description' => 'View registered users.',
            ],

            [
                'name' => 'users.create',
                'display_name' => 'Create Users',
                'description' => 'Create new users.',
            ],

            [
                'name' => 'users.update',
                'display_name' => 'Update Users',
                'description' => 'Update user information.',
            ],

            [
                'name' => 'users.delete',
                'display_name' => 'Delete Users',
                'description' => 'Delete users.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'roles.view',
                'display_name' => 'View Roles',
                'description' => 'View roles.',
            ],

            [
                'name' => 'roles.create',
                'display_name' => 'Create Roles',
                'description' => 'Create new roles.',
            ],

            [
                'name' => 'roles.update',
                'display_name' => 'Update Roles',
                'description' => 'Update roles and permissions.',
            ],

            [
                'name' => 'roles.delete',
                'display_name' => 'Delete Roles',
                'description' => 'Delete roles.',
            ],

            [
                'name' => 'roles.manage',
                'display_name' => 'Manage Roles',
                'description' => 'Manage roles and their permissions.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Implementation Leads
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'leads.view',
                'display_name' => 'View Implementation Leads',
                'description' => 'View implementation requests and leads.',
            ],

            [
                'name' => 'leads.create',
                'display_name' => 'Create Implementation Leads',
                'description' => 'Create implementation leads.',
            ],

            [
                'name' => 'leads.update',
                'display_name' => 'Update Implementation Leads',
                'description' => 'Update implementation lead information.',
            ],

            [
                'name' => 'leads.delete',
                'display_name' => 'Delete Implementation Leads',
                'description' => 'Delete implementation leads.',
            ],

            [
                'name' => 'leads.manage',
                'display_name' => 'Manage Implementation Leads',
                'description' => 'Manage implementation requests and leads.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Reviews
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'reviews.view',
                'display_name' => 'View Reviews',
                'description' => 'View software reviews.',
            ],

            [
                'name' => 'reviews.create',
                'display_name' => 'Create Reviews',
                'description' => 'Create software reviews.',
            ],

            [
                'name' => 'reviews.update',
                'display_name' => 'Update Reviews',
                'description' => 'Update and moderate software reviews.',
            ],

            [
                'name' => 'reviews.delete',
                'display_name' => 'Delete Reviews',
                'description' => 'Delete software reviews.',
            ],

            [
                'name' => 'reviews.manage',
                'display_name' => 'Manage Reviews',
                'description' => 'Manage and moderate software reviews.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Partners
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'partners.view',
                'display_name' => 'View Partners',
                'description' => 'View platform partners.',
            ],

            [
                'name' => 'partners.create',
                'display_name' => 'Create Partners',
                'description' => 'Create new partners.',
            ],

            [
                'name' => 'partners.update',
                'display_name' => 'Update Partners',
                'description' => 'Update partner information.',
            ],

            [
                'name' => 'partners.delete',
                'display_name' => 'Delete Partners',
                'description' => 'Delete partners.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Articles
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'articles.view',
                'display_name' => 'View Articles',
                'description' => 'View articles.',
            ],

            [
                'name' => 'articles.create',
                'display_name' => 'Create Articles',
                'description' => 'Create new articles.',
            ],

            [
                'name' => 'articles.update',
                'display_name' => 'Update Articles',
                'description' => 'Update articles.',
            ],

            [
                'name' => 'articles.delete',
                'display_name' => 'Delete Articles',
                'description' => 'Delete articles.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Tutorials
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'tutorials.view',
                'display_name' => 'View Tutorials',
                'description' => 'View tutorials.',
            ],

            [
                'name' => 'tutorials.create',
                'display_name' => 'Create Tutorials',
                'description' => 'Create new tutorials.',
            ],

            [
                'name' => 'tutorials.update',
                'display_name' => 'Update Tutorials',
                'description' => 'Update tutorials.',
            ],

            [
                'name' => 'tutorials.delete',
                'display_name' => 'Delete Tutorials',
                'description' => 'Delete tutorials.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Case Studies
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'case-studies.view',
                'display_name' => 'View Case Studies',
                'description' => 'View case studies.',
            ],

            [
                'name' => 'case-studies.create',
                'display_name' => 'Create Case Studies',
                'description' => 'Create new case studies.',
            ],

            [
                'name' => 'case-studies.update',
                'display_name' => 'Update Case Studies',
                'description' => 'Update case studies.',
            ],

            [
                'name' => 'case-studies.delete',
                'display_name' => 'Delete Case Studies',
                'description' => 'Delete case studies.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Affiliate Programs
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'affiliate-programs.view',
                'display_name' => 'View Affiliate Programs',
                'description' => 'View affiliate programs.',
            ],

            [
                'name' => 'affiliate-programs.create',
                'display_name' => 'Create Affiliate Programs',
                'description' => 'Create affiliate programs.',
            ],

            [
                'name' => 'affiliate-programs.update',
                'display_name' => 'Update Affiliate Programs',
                'description' => 'Update affiliate programs.',
            ],

            [
                'name' => 'affiliate-programs.delete',
                'display_name' => 'Delete Affiliate Programs',
                'description' => 'Delete affiliate programs.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Affiliate Links
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'affiliate-links.view',
                'display_name' => 'View Affiliate Links',
                'description' => 'View affiliate links.',
            ],

            [
                'name' => 'affiliate-links.create',
                'display_name' => 'Create Affiliate Links',
                'description' => 'Create affiliate links.',
            ],

            [
                'name' => 'affiliate-links.update',
                'display_name' => 'Update Affiliate Links',
                'description' => 'Update affiliate links.',
            ],

            [
                'name' => 'affiliate-links.delete',
                'display_name' => 'Delete Affiliate Links',
                'description' => 'Delete affiliate links.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Commission
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'commission.view',
                'display_name' => 'View Commission',
                'description' => 'View affiliate commission data.',
            ],

            [
                'name' => 'commission.manage',
                'display_name' => 'Manage Commission',
                'description' => 'Manage affiliate commission data.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Analytics
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'analytics.view',
                'display_name' => 'View Analytics',
                'description' => 'View platform analytics.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'settings.view',
                'display_name' => 'View Settings',
                'description' => 'View system settings.',
            ],

            [
                'name' => 'settings.manage',
                'display_name' => 'Manage Settings',
                'description' => 'Manage system settings.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Audit Logs
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'audit-logs.view',
                'display_name' => 'View Audit Logs',
                'description' => 'View system audit logs.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Admin Leads
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'admin-leads.view',
                'display_name' => 'View Admin Leads',
                'description' => 'View administrative lead data.',
            ],

            [
                'name' => 'admin-leads.manage',
                'display_name' => 'Manage Admin Leads',
                'description' => 'Manage administrative lead data.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                ],
                [
                    'display_name' => $permission['display_name'],
                    'description' => $permission['description'],
                ]
            );
        }
    }
}