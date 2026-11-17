<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::first();

        $accounts = [
            [
                'name' => 'Quản Lý Quán',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Bếp Trưởng Nguyễn Văn An',
                'username' => 'kitchen',
                'password' => Hash::make('password'),
                'role' => 'kitchen',
            ],
            [
                'name' => 'Phục Vụ Lê Thị Bình',
                'username' => 'waiter',
                'password' => Hash::make('password'),
                'role' => 'waiter',
            ],
            [
                'name' => 'Thu Ngân Trần Thu Thảo',
                'username' => 'cashier',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ],
        ];

        foreach ($accounts as $acc) {
            Staff::updateOrCreate(
                ['username' => $acc['username']],
                array_merge($acc, ['restaurant_id' => $restaurant->id, 'is_active' => true])
            );
        }
    }
}
