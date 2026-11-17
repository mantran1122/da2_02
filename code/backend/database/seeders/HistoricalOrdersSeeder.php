<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Table;
use App\Models\DiningSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Support\Str;

class HistoricalOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $tables = Table::all();
        $dishes = Dish::all();

        if ($tables->isEmpty() || $dishes->isEmpty()) {
            return;
        }

        // Tạo 300 phiên ăn giả lập đã đóng
        $totalSessions = 300;

        // Giả lập thói quen thường gọi chung các cặp món:
        // Cặp 1: Phở bò (id 1) + Quẩy/Nem rán (id 6) + Trà đá (id 9)
        // Cặp 2: Bún chả (id 3) + Nem rán (id 6) + Trà tắc (id 10)
        // Cặp 3: Cơm sườn (id 4) + Trứng ốp la (id 8) + Canh/Trà đá (id 9)
        $dishIds = $dishes->pluck('id')->all();

        for ($i = 1; $i <= $totalSessions; $i++) {
            $table = $tables->random();
            $openedTime = now()->subDays(rand(1, 30))->subHours(rand(1, 12));

            $session = DiningSession::create([
                'table_id' => $table->id,
                'session_token' => Str::random(64),
                'status' => 'closed',
                'customer_count' => rand(1, 4),
                'opened_at' => $openedTime,
                'closed_at' => (clone $openedTime)->addMinutes(rand(30, 90)),
            ]);

            // Mỗi phiên có 1 - 2 đợt gọi món
            $ordersCount = rand(1, 2);
            for ($o = 1; $o <= $ordersCount; $o++) {
                $order = Order::create([
                    'session_id' => $session->id,
                    'order_no' => $o,
                    'status' => 'completed',
                    'created_at' => (clone $openedTime)->addMinutes($o * 10),
                ]);

                // Chọn ngẫu nhiên 2 - 4 món trong đơn
                $numItems = rand(2, 4);
                $selectedDishIds = (array) array_rand(array_flip($dishIds), $numItems);

                // Tăng xác suất xuất hiện cặp combo quen thuộc
                if (rand(1, 100) <= 60 && count($dishIds) >= 9) {
                    $selectedDishIds = array_unique(array_merge($selectedDishIds, [$dishIds[0], $dishIds[min(5, count($dishIds)-1)]]));
                }

                foreach ($selectedDishIds as $dId) {
                    $dish = $dishes->firstWhere('id', $dId);
                    if ($dish) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'session_id' => $session->id,
                            'dish_id' => $dish->id,
                            'quantity' => rand(1, 2),
                            'unit_price' => $dish->price,
                            'status' => 'served',
                            'created_at' => $order->created_at,
                        ]);
                    }
                }
            }
        }
    }
}
