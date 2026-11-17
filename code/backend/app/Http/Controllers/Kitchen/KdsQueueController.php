<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Services\ItemStateMachine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class KdsQueueController extends Controller
{
    public function __construct(
        protected ItemStateMachine $stateMachine,
        protected StockService $stockService
    ) {}

    /**
     * Hàng đợi món bếp KDS: Lọc các món đang chờ/nấu, sắp xếp theo FIFO
     */
    public function queue(Request $request): JsonResponse
    {
        $status = $request->query('status'); // 'pending', 'cooking' hoặc all active
        $query = OrderItem::with(['dish', 'order', 'session.table.area'])
            ->whereIn('status', $status ? [$status] : ['pending', 'cooking']);

        $items = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Đổi trạng thái món theo quy tắc 1 chiều
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $item = OrderItem::findOrFail($id);
        $nextStatus = $request->input('status');

        try {
            $updated = $this->stateMachine->transition($item, $nextStatus);

            return response()->json([
                'success' => true,
                'message' => "Đã chuyển trạng thái món sang {$nextStatus}.",
                'data' => $updated
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Bếp hủy món (bắt buộc kèm lý do, tự động hoàn kho nguyên liệu)
     */
    public function cancelItem(Request $request, int $id): JsonResponse
    {
        $item = OrderItem::with('dish')->findOrFail($id);
        $reason = $request->input('reason');

        if (! $reason) {
            return response()->json([
                'success' => false,
                'message' => 'Bắt buộc phải nhập lý do khi bếp hủy món.'
            ], 422);
        }

        try {
            $this->stateMachine->transition($item, 'cancelled', $reason);
            $this->stockService->restoreForDish($item->dish, $item->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy món và hoàn trả nguyên liệu về kho.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Thống kê nhanh hiệu suất bếp (số món chờ, đang nấu, thời gian chờ TB)
     */
    public function stats(): JsonResponse
    {
        $pendingCount = OrderItem::where('status', 'pending')->count();
        $cookingCount = OrderItem::where('status', 'cooking')->count();

        // Tính thời gian chờ trung bình của các món đang pending (tính theo phút)
        $avgWaitMinutes = OrderItem::where('status', 'pending')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as avg_wait')
            ->value('avg_wait') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'pending_count' => $pendingCount,
                'cooking_count' => $cookingCount,
                'avg_wait_minutes' => round((float) $avgWaitMinutes, 1),
            ]
        ]);
    }
}
