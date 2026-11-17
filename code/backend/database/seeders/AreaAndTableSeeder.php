<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Area;
use App\Models\Table;

class AreaAndTableSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::first();

        $areas = [
            'Tầng 1' => 10,
            'Sân vườn' => 6,
            'Phòng VIP' => 4,
        ];

        $tableNumber = 1;

        foreach ($areas as $areaName => $count) {
            $area = Area::firstOrCreate([
                'restaurant_id' => $restaurant->id,
                'name' => $areaName,
            ]);

            for ($i = 1; $i <= $count; $i++) {
                $code = sprintf('B%02d', $tableNumber);
                // Giữ token bàn B12 cố định cho kịch bản demo: a1b2c3d4-e5f6-7890-abcd-ef1234567890
                $token = ($code === 'B12')
                    ? 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
                    : md5("table_{$code}_salt_2026");

                Table::firstOrCreate(
                    ['code' => $code],
                    [
                        'area_id' => $area->id,
                        'capacity' => ($areaName === 'Phòng VIP') ? 8 : 4,
                        'qr_token' => $token,
                        'is_active' => true,
                    ]
                );
                $tableNumber++;
            }
        }
    }
}
