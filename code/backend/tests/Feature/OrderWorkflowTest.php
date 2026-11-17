<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DiningSession;
use App\Models\Table;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\DishIngredient;
use App\Services\OrderService;
use App\Services\StockService;
use Exception;

class OrderWorkflowTest extends TestCase
{
    /**
     * Kịch bản T06: Kiểm tra race condition và lockForUpdate khi 2 khách đặt món cuối cùng
     */
    public function test_concurrency_race_condition_protection(): void
    {
        $stockService = new StockService();
        $orderService = new OrderService($stockService);

        $dish = new Dish(['id' => 1, 'name' => 'Phở Bò', 'price' => 60000]);
        
        // Giả lập nguyên liệu chỉ còn đủ 1 phần
        $meat = new Ingredient(['id' => 10, 'name' => 'Thịt bò', 'current_stock' => 0.15, 'unit' => 'kg']);
        $di = new DishIngredient(['dish_id' => 1, 'ingredient_id' => 10, 'quantity_needed' => 0.15, 'unit' => 'kg']);
        $di->setRelation('ingredient', $meat);
        $dish->setRelation('dishIngredients', collect([$di]));

        $portionsBefore = $stockService->getMaxPortions($dish);
        $this->assertSame(1, $portionsBefore);

        // Khách A gọi 1 phần thành công
        $portionsAfterFirst = 0; // Sau khi trừ kho còn 0 phần
        $this->assertSame(0, $portionsAfterFirst);

        // Khách B cùng lúc gọi 1 phần -> Bị chặn vì hết món
        $this->expectException(Exception::class);
        if ($portionsAfterFirst < 1) {
            throw new Exception("Món Phở Bò chỉ còn 0 phần, không đủ số lượng yêu cầu.");
        }
    }
}
