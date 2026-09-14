<?php

namespace Database\Seeders;

use App\Models\Software;
use Illuminate\Database\Seeder;

class SoftwareSeeder extends Seeder
{
    public function run(): void
    {
        Software::insert([
            [
                'name' => 'Zoho CRM',
                'slug' => 'zoho-crm',
                'category' => 'CRM',
                'rating' => 4.8,
                'price' => '$20/user/mo',
                'views' => 24560,
                'fit' => '92%',
                'tag' => 'Best Value',
                'description' => 'Cloud CRM for sales teams with automation, pipeline management and reporting.',
                'is_active' => true,
            ],
            [
                'name' => 'HubSpot CRM',
                'slug' => 'hubspot-crm',
                'category' => 'CRM',
                'rating' => 4.7,
                'price' => 'Free – $150/mo',
                'views' => 18230,
                'fit' => '89%',
                'tag' => 'Popular',
                'description' => 'CRM platform for managing contacts, sales pipelines and customer relationships.',
                'is_active' => true,
            ],
            [
                'name' => 'Salesforce Sales Cloud',
                'slug' => 'salesforce-sales-cloud',
                'category' => 'CRM',
                'rating' => 4.6,
                'price' => '$25/user/mo',
                'views' => 15890,
                'fit' => '87%',
                'tag' => 'Enterprise',
                'description' => 'Enterprise CRM platform for sales management, automation and analytics.',
                'is_active' => true,
            ],
            [
                'name' => 'Odoo ERP',
                'slug' => 'odoo-erp',
                'category' => 'ERP',
                'rating' => 4.5,
                'price' => '$24/user/mo',
                'views' => 13450,
                'fit' => '84%',
                'tag' => 'Flexible',
                'description' => 'Flexible business management platform with ERP and integrated applications.',
                'is_active' => true,
            ],
            [
                'name' => 'Microsoft Dynamics 365',
                'slug' => 'microsoft-dynamics-365',
                'category' => 'ERP',
                'rating' => 4.4,
                'price' => '$65/user/mo',
                'views' => 11230,
                'fit' => '81%',
                'tag' => 'Enterprise',
                'description' => 'Business applications platform for CRM, ERP and enterprise operations.',
                'is_active' => true,
            ],
            [
                'name' => 'BambooHR',
                'slug' => 'bamboohr',
                'category' => 'HR',
                'rating' => 4.6,
                'price' => '$8.25/user/mo',
                'views' => 9840,
                'fit' => '86%',
                'tag' => 'SMB',
                'description' => 'Human resources software for employee management and people operations.',
                'is_active' => true,
            ],
        ]);
    }
}