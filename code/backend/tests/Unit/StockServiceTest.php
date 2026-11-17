<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\StockService;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\DishIngredient;

class StockServiceTest extends TestCase
{
    protected StockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockService();
    }

    /**
     * Test tính số phần ăn tối đa (maxPortions) dựa trên định lượng nguyên liệu
     */
    public function test_calculate_max_portions(): void
    {
        $dish = new Dish(['name' => 'Phở Bò']);
        
        $meat = new Ingredient(['name' => 'Thịt bò', 'current_stock' => 1.5, 'unit' => 'kg']); // 1.5kg
        $noodle = new Ingredient(['name' => 'Bánh phở', 'current_stock' => 5.0, 'unit' => 'kg']); // 5.0kg

        $diMeat = new DishIngredient(['quantity_needed' => 0.15, 'unit' => 'kg']); // Cần 0.15kg/bát -> được 10 bát
        $diMeat->setRelation('ingredient', $meat);

        $diNoodle = new DishIngredient(['quantity_needed' => 0.20, 'unit' => 'kg']); // Cần 0.20kg/bát -> được 25 bát
        $diNoodle->setRelation('ingredient', $noodle);

        $dish->setRelation('dishIngredients', collect([$diMeat, $diNoodle]));

        $max = $this->service->getMaxPortions($dish);

        // Số phần tối đa bị giới hạn bởi thịt bò (min(10, 25) = 10)
        $this->assertSame(10, $max);
    }
}
