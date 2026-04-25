<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // Default Settings
        // ============================================
        $settings = [
            ['key' => 'store_name', 'value' => 'CargoMind', 'label' => 'Nama Toko', 'type' => 'text'],
            ['key' => 'store_address', 'value' => 'Jl. Contoh No. 123, Jakarta', 'label' => 'Alamat Toko', 'type' => 'textarea'],
            ['key' => 'store_phone', 'value' => '021-12345678', 'label' => 'Telepon Toko', 'type' => 'text'],
            ['key' => 'receipt_font_size', 'value' => '14', 'label' => 'Ukuran Font Nota (pt)', 'type' => 'number'],
            ['key' => 'receipt_footer', 'value' => 'Terima kasih telah berbelanja di CargoMind', 'label' => 'Footer Nota', 'type' => 'text'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }

        // ============================================
        // Default Users
        // ============================================
        User::updateOrCreate(
            ['email' => 'master@cargomind.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('password'),
                'role' => 'master',
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@cargomind.com'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        User::updateOrCreate(
            ['email' => 'karyawan@cargomind.com'],
            [
                'name' => 'Karyawan',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
            ]
        );

        // ============================================
        // Sample Items
        // ============================================
        $items = [
            ['name' => 'Baut 10mm', 'sku' => 'BT-010', 'price' => 2500, 'stock' => 500, 'rack_primary' => 'A-01', 'rack_secondary' => 'B-03'],
            ['name' => 'Mur 8mm', 'sku' => 'MR-008', 'price' => 1500, 'stock' => 800, 'rack_primary' => 'A-01', 'rack_secondary' => null],
            ['name' => 'Paku 5cm', 'sku' => 'PK-050', 'price' => 500, 'stock' => 2000, 'rack_primary' => 'A-02', 'rack_secondary' => 'C-01'],
            ['name' => 'Kunci Inggris 12"', 'sku' => 'KI-012', 'price' => 85000, 'stock' => 30, 'rack_primary' => 'B-01', 'rack_secondary' => null],
            ['name' => 'Obeng Set (+/-)', 'sku' => 'OB-SET', 'price' => 45000, 'stock' => 50, 'rack_primary' => 'B-02', 'rack_secondary' => 'D-01'],
            ['name' => 'Cat Tembok Putih 5kg', 'sku' => 'CT-W05', 'price' => 125000, 'stock' => 25, 'rack_primary' => 'C-01', 'rack_secondary' => null],
            ['name' => 'Selang Air 10m', 'sku' => 'SL-010', 'price' => 75000, 'stock' => 40, 'rack_primary' => 'C-02', 'rack_secondary' => 'A-03'],
            ['name' => 'Gembok 50mm', 'sku' => 'GB-050', 'price' => 35000, 'stock' => 60, 'rack_primary' => 'D-01', 'rack_secondary' => null],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['sku' => $item['sku']], $item);
        }
    }
}
