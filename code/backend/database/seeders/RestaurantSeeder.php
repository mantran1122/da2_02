<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        Restaurant::firstOrCreate(
            ['name' => 'Quán Bếp Nhỏ'],
            [
                'address' => '123 Đường Ẩm Thực, Quận 1, TP. Hồ Chí Minh',
                'phone' => '0901234567',
                'vat_percent' => 8.00,
                'service_charge_percent' => 0.00,
                'open_hours' => '08:00 - 22:00',
            ]
        );
    }
}
