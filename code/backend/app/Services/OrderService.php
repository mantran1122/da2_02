<?php

namespace App\Services;

use App\Models\DiningSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Tạo order mới trong phiên ăn với lockForUpdate và kiểm tra tồn kho nguyên liệu
     */
    public function createOrder(DiningSession $session, array $itemsData, ?string $generalNote = null): Order
    {
        return DB::transaction(function () use ($session, $itemsData, $generalNote) {
            // 1. Kiểm tra tồn kho nguyên liệu cho từng món trước khi tạo
            foreach ($itemsData as $item) {
                $dish = Dish::findOrFail($item['dish_id']);
                $qty = (int) $item['quantity'];

                $availablePortions = $this->stockService->getMaxPortions($dish);
                if ($availablePortions !== null && $availablePortions < $qty) {
                    throw new Exception("Món {$dish->name} chỉ còn {$availablePortions} phần, không đủ số lượng yêu cầu.");
                }
            }

            // 2. Tính số thứ tự order_no trong phiên
            $nextOrderNo = (Order::where('session_id', $session->id)->max('order_no') ?? 0) + 1;

            $order = Order::create([
                'session_id' => $session->id,
                'order_no' => $nextOrderNo,
                'status' => 'received',
                'note' => $generalNote,
            ]);

            // 3. Tạo các order_items và trừ kho nguyên liệu
            $createdItems = [];
            foreach ($itemsData as $item) {
                $dish = Dish::findOrFail($item['dish_id']);
                $qty = (int) $item['quantity'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'session_id' => $session->id,
                    'dish_id' => $dish->id,
                    'quantity' => $qty,
                    'unit_price' => $dish->price, // Snapshot giá tại thời điểm gọi
                    'note' => $item['note'] ?? null,
                    'status' => 'pending',
                ]);

                // Trừ kho nguyên liệu
                $this->stockService->deductForDish($dish, $qty);
                $createdItems[] = $orderItem;
            }

            // 4. Phát sóng sự kiện WebSocket OrderCreated xuống KDS Bếp
            broadcast(new OrderCreated($order->load(['items.dish', 'session.table'])))->toOthers();

            return $order;
        });
    }
}
