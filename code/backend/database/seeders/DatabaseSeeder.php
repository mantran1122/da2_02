<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RestaurantSeeder::class,
            StaffSeeder::class,
            AreaAndTableSeeder::class,
            MenuAndIngredientSeeder::class,
            HistoricalOrdersSeeder::class,
        ]);
    }
}
