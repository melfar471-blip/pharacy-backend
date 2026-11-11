<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Medicine;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء مستخدمين (مدير وكاشير)
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@pharmacy.com',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Cashier One',
            'email' => 'cashier@pharmacy.com',
            'password' => Hash::make('password123'),
            'role' => 'cashier'
        ]);

        // 2. إضافة بعض الأدوية التجريبية
        Medicine::create([
            'barcode' => '6221234567890',
            'name' => 'Panadol Extra 500mg',
            'category' => 'مسكنات',
            'quantity' => 150,
            'alert_threshold' => 10,
            'purchase_price' => 35.00,
            'selling_price' => 45.00,
            'expiry_date' => '2027-12-31',
            'supplier_name' => 'شركة الأدوية المتحدة'
        ]);

        Medicine::create([
            'barcode' => '6220987654321',
            'name' => 'Augmentin 1g Tablets',
            'category' => 'مضاد حيوي',
            'quantity' => 40,
            'alert_threshold' => 5,
            'purchase_price' => 80.00,
            'selling_price' => 95.00,
            'expiry_date' => '2026-08-15',
            'supplier_name' => 'المجموعة المصرية للأدوية'
        ]);
    }
}
?>
