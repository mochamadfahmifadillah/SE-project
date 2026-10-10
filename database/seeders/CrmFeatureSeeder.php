<?php

namespace Database\Seeders;

use App\Models\Software;
use App\Models\SoftwareFeature;
use Illuminate\Database\Seeder;
use RuntimeException;

class CrmFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'contact-management' => [
                'name' => 'Contact Management',
                'description' => 'Mengelola informasi dan riwayat kontak pelanggan.',
            ],
            'lead-management' => [
                'name' => 'Lead Management',
                'description' => 'Mengelola prospek dan tindak lanjut penjualan.',
            ],
            'sales-pipeline' => [
                'name' => 'Sales Pipeline Management',
                'description' => 'Memantau tahapan transaksi penjualan.',
            ],
            'reports-dashboards' => [
                'name' => 'Reports & Dashboards',
                'description' => 'Menyajikan laporan dan visualisasi kinerja.',
            ],
        ];

        $features = collect($definitions)->map(
            function (array $definition, string $slug) {
                return SoftwareFeature::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'is_active' => true,
                    ]
                );
            }
        );

        $slugs = [
            'zoho-crm',
            'hubspot-crm',
            'salesforce-sales-cloud',
        ];

        $software = Software::whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        $missing = array_diff($slugs, $software->keys()->all());

        if ($missing !== []) {
            throw new RuntimeException(
                'Software tidak ditemukan: ' . implode(', ', $missing)
            );
        }

        foreach ($software as $product) {
            $product->features()->syncWithoutDetaching(
                $features->pluck('id')->all()
            );
        }

        $this->command?->info('CRM feature seeding completed.');
    }
}
