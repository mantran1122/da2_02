<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Services\StockService;
use App\Services\ItemStateMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService,
        protected ItemStateMachine $stateMachine,
    ) {}

    /**
     * Khách gửi danh sách món xuống bếp
     */
    public function store(Request $request): JsonResponse
    {
        $session = $request->get('current_session');

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.dish_id' => 'required|integer|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'items.*.note' => 'nullable|string|max:150',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $order = $this->orderService->createOrder(
                $session,
                $validated['items'],
                $validated['note'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu món xuống bếp thành công!',
                'data' => $order->load('items.dish')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Lấy danh sách các đơn gọi món của phiên
     */
    public function index(Request $request): JsonResponse
    {
        $session = $request->get('current_session');
        $orders = Order::with('items.dish')
            ->where('session_id', $session->id)
            ->orderBy('order_no', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Xem trạng thái tất cả món ăn trong phiên hiện tại (phục vụ màn hình theo dõi đơn)
     */
    public function items(Request $request): JsonResponse
    {
        $session = $request->get('current_session');
        $items = OrderItem::with('dish')
            ->where('session_id', $session->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Khách xin hủy món (chỉ được phép khi món còn ở trạng thái pending)
     */
    public function cancelItem(Request $request, int $orderId, int $itemId): JsonResponse
    {
        $session = $request->get('current_session');
        $item = OrderItem::where('id', $itemId)
            ->where('session_id', $session->id)
            ->firstOrFail();

        if ($item->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Món đã bắt đầu chế biến hoặc đã phục vụ, không thể tự hủy. Vui lòng liên hệ nhân viên.'
            ], 400);
        }

        try {
            $this->stateMachine->transition($item, 'cancelled', 'Khách hủy món');
            $this->stockService->restoreForDish($item->dish, $item->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy món thành công.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
