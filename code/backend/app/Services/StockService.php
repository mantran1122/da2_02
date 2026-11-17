<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Events\DishOutOfStock;

class StockService
{
    /**
     * Tính số phần ăn tối đa có thể phục vụ dựa trên định lượng nguyên liệu
     */
    public function getMaxPortions(Dish $dish): ?int
    {
        $dish->loadMissing('dishIngredients.ingredient');

        if ($dish->dishIngredients->isEmpty()) {
            return null; // Không cấu hình định lượng, coi như luôn có hàng
        }

        $minPortions = PHP_INT_MAX;

        foreach ($dish->dishIngredients as $di) {
            $ingredient = $di->ingredient;
            if (! $ingredient) continue;

            $needed = (float) $di->quantity_needed;
            if ($needed <= 0) continue;

            $possible = (int) floor((float) $ingredient->current_stock / $needed);
            if ($possible < $minPortions) {
                $minPortions = $possible;
            }
        }

        return max(0, $minPortions);
    }

    /**
     * Trừ kho nguyên liệu khi order được duyệt
     */
    public function deductForDish(Dish $dish, int $quantity): void
    {
        $dish->loadMissing('dishIngredients.ingredient');

        foreach ($dish->dishIngredients as $di) {
            $ingredient = Ingredient::where('id', $di->ingredient_id)->lockForUpdate()->first();
            if ($ingredient) {
                $deductAmount = (float) $di->quantity_needed * $quantity;
                $newStock = max(0, (float) $ingredient->current_stock - $deductAmount);
                $ingredient->update(['current_stock' => $newStock]);

                // Nếu chạm ngưỡng tối thiểu -> Báo hết món
                if ($newStock <= (float) $ingredient->minimum_threshold) {
                    $dish->update(['status' => 'out_of_stock']);
                    broadcast(new DishOutOfStock($dish))->toOthers();
                }
            }
        }
    }

    /**
     * Hoàn kho nguyên liệu khi món bị hủy
     */
    public function restoreForDish(Dish $dish, int $quantity): void
    {
        $dish->loadMissing('dishIngredients.ingredient');

        foreach ($dish->dishIngredients as $di) {
            $ingredient = Ingredient::where('id', $di->ingredient_id)->lockForUpdate()->first();
            if ($ingredient) {
                $restoreAmount = (float) $di->quantity_needed * $quantity;
                $ingredient->increment('current_stock', $restoreAmount);
            }
        }
    }
}
