<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ComboRule;
use Illuminate\Support\Facades\DB;

class FpGrowthService
{
    /**
     * Khai phá luật kết hợp món ăn kèm (A -> B) từ lịch sử đơn hàng
     * Ngưỡng tối thiểu: minSupport = 0.05, minConfidence = 0.30
     */
    public function mineAssociationRules(float $minSupport = 0.05, float $minConfidence = 0.30): int
    {
        // 1. Lấy tất cả giỏ hàng (danh sách dish_id trong từng order)
        $orders = Order::with('items')->has('items', '>=', 2)->get();
        $totalTransactions = $orders->count();

        if ($totalTransactions < 5) {
            return 0; // Chưa đủ dữ liệu để khai phá
        }

        $itemCounts = [];
        $pairCounts = [];

        foreach ($orders as $order) {
            $dishIds = $order->items->pluck('dish_id')->unique()->values()->all();
            sort($dishIds);

            // Đếm tần suất từng món
            foreach ($dishIds as $id) {
                $itemCounts[$id] = ($itemCounts[$id] ?? 0) + 1;
            }

            // Đếm tần suất cặp món (A, B)
            $count = count($dishIds);
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $pairKey = $dishIds[$i] . '_' . $dishIds[$j];
                    $pairCounts[$pairKey] = ($pairCounts[$pairKey] ?? 0) + 1;
                }
            }
        }

        $newRulesCount = 0;

        // 2. Tính toán Support, Confidence và Lift cho từng chiều (A -> B và B -> A)
        foreach ($pairCounts as $pairKey => $frequency) {
            [$dishA, $dishB] = explode('_', $pairKey);
            $supportAB = $frequency / $totalTransactions;

            if ($supportAB < $minSupport) {
                continue;
            }

            // Chiều A -> B
            $confAtoB = $frequency / ($itemCounts[$dishA] ?? 1);
            if ($confAtoB >= $minConfidence) {
                $supportB = ($itemCounts[$dishB] ?? 1) / $totalTransactions;
                $liftAtoB = $confAtoB / max(0.0001, $supportB);

                ComboRule::updateOrCreate(
                    [
                        'antecedent_dish_id' => $dishA,
                        'consequent_dish_id' => $dishB,
                    ],
                    [
                        'support' => round($supportAB, 4),
                        'confidence' => round($confAtoB, 4),
                        'lift' => round($liftAtoB, 4),
                        'source' => 'fp_growth',
                        'is_active' => true,
                    ]
                );
                $newRulesCount++;
            }

            // Chiều B -> A
            $confBtoA = $frequency / ($itemCounts[$dishB] ?? 1);
            if ($confBtoA >= $minConfidence) {
                $supportA = ($itemCounts[$dishA] ?? 1) / $totalTransactions;
                $liftBtoA = $confBtoA / max(0.0001, $supportA);

                ComboRule::updateOrCreate(
                    [
                        'antecedent_dish_id' => $dishB,
                        'consequent_dish_id' => $dishA,
                    ],
                    [
                        'support' => round($supportAB, 4),
                        'confidence' => round($confBtoA, 4),
                        'lift' => round($liftBtoA, 4),
                        'source' => 'fp_growth',
                        'is_active' => true,
                    ]
                );
                $newRulesCount++;
            }
        }

        return $newRulesCount;
    }
}
