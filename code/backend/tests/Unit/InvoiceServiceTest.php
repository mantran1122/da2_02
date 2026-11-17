<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\InvoiceService;
use App\Models\DiningSession;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Area;
use App\Models\Restaurant;
use Mockery;

class InvoiceServiceTest extends TestCase
{
    protected InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test ca vàng Golden Case 145.000 đ
     * Subtotal: 145.000 đ
     * Giảm giá: 14.500 đ (10%) -> Sau giảm: 130.500 đ
     * Phí phục vụ: 5% * 130.500 = 6.525 đ
     * Thuế VAT: 8% * (130.500 + 6.525) = 8% * 137.025 = 10.962 đ
     * Tổng cộng: 137.025 + 10.962 = 147.987 đ
     */
    public function test_golden_case_145k_calculation(): void
    {
        $restaurant = new Restaurant([
            'vat_percent' => 8.0,
            'service_charge_percent' => 5.0,
        ]);

        $area = new Area();
        $area->setRelation('restaurant', $restaurant);

        $table = new Table();
        $table->setRelation('area', $area);

        $session = new DiningSession();
        $session->setRelation('table', $table);

        $items = collect([
            new OrderItem(['unit_price' => 60000, 'quantity' => 2, 'status' => 'served']), // Phở bò: 120k
            new OrderItem(['unit_price' => 5000, 'quantity' => 1, 'status' => 'served']),   // Trà đá: 5k
            new OrderItem(['unit_price' => 20000, 'quantity' => 1, 'status' => 'served']),  // Sữa chua: 20k
        ]);
        $session->setRelation('orderItems', $items);

        $calc = $this->service->calculateForSession($session, discount: 14500);

        $this->assertSame(145000, $calc['subtotal']);
        $this->assertSame(14500, $calc['discount']);
        $this->assertSame(130500, $calc['afterDiscount']);
        $this->assertSame(6525, $calc['serviceCharge']);
        $this->assertSame(137025, $calc['vatBase']);
        $this->assertSame(10962, $calc['vat']);
        $this->assertSame(147987, $calc['total']);
    }

    /**
     * Test món bị hủy không được tính vào tổng tiền
     */
    public function test_cancelled_items_are_excluded_from_subtotal(): void
    {
        $restaurant = new Restaurant(['vat_percent' => 0.0, 'service_charge_percent' => 0.0]);
        $area = new Area();
        $area->setRelation('restaurant', $restaurant);
        $table = new Table();
        $table->setRelation('area', $area);
        $session = new DiningSession();
        $session->setRelation('table', $table);

        $items = collect([
            new OrderItem(['unit_price' => 60000, 'quantity' => 1, 'status' => 'served']),
            new OrderItem(['unit_price' => 50000, 'quantity' => 1, 'status' => 'cancelled']), // Đã hủy
        ]);
        $session->setRelation('orderItems', $items);

        $calc = $this->service->calculateForSession($session);
        $this->assertSame(60000, $calc['subtotal']);
    }

    /**
     * Test VAT được tính sau khi đã cộng phí phục vụ (VAT base = afterDiscount + serviceCharge)
     */
    public function test_vat_applies_after_service_charge(): void
    {
        $restaurant = new Restaurant(['vat_percent' => 10.0, 'service_charge_percent' => 5.0]);
        $area = new Area();
        $area->setRelation('restaurant', $restaurant);
        $table = new Table();
        $table->setRelation('area', $area);
        $session = new DiningSession();
        $session->setRelation('table', $table);

        $items = collect([
            new OrderItem(['unit_price' => 100000, 'quantity' => 1, 'status' => 'served']),
        ]);
        $session->setRelation('orderItems', $items);

        $calc = $this->service->calculateForSession($session);
        // Đúng: 100.000 -> serviceCharge 5.000 -> vatBase 105.000 -> vat 10.500 -> total 115.500
        $this->assertSame(100000, $calc['subtotal']);
        $this->assertSame(5000, $calc['serviceCharge']);
        $this->assertSame(105000, $calc['vatBase']);
        $this->assertSame(10500, $calc['vat']);
        $this->assertSame(115500, $calc['total']);
    }
}
