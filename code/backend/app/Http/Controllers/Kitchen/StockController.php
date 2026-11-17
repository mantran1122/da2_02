<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Events\DishOutOfStock;
use App\Events\DishBackInStock;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    /**
     * Bếp báo hết món nhanh 1 chạm
     */
    public function markOutOfStock(int $id): JsonResponse
    {
        $dish = Dish::findOrFail($id);
        $dish->update(['status' => 'out_of_stock']);

        broadcast(new DishOutOfStock($dish))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "Đã chuyển món {$dish->name} sang trạng thái Hết món.",
            'data' => $dish
        ]);
    }

    /**
     * Bếp báo món đã có lại
     */
    public function markBackInStock(int $id): JsonResponse
    {
        $dish = Dish::findOrFail($id);
        $dish->update(['status' => 'available']);

        broadcast(new DishBackInStock($dish))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "Đã mở lại món {$dish->name}.",
            'data' => $dish
        ]);
    }
}
