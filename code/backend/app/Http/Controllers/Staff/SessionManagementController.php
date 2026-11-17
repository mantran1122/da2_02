<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DiningSession;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SessionManagementController extends Controller
{
    /**
     * Danh sách tất cả các phiên ăn đang mở
     */
    public function index(): JsonResponse
    {
        $sessions = DiningSession::with(['table.area', 'orderItems.dish'])
            ->where('status', 'open')
            ->orderBy('opened_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Mở phiên thủ công cho khách không dùng smartphone
     */
    public function storeManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:tables,id',
            'customer_count' => 'nullable|integer|min:1',
        ]);

        $existing = DiningSession::where('table_id', $validated['table_id'])
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn này đang có phiên ăn mở.'
            ], 422);
        }

        $session = DiningSession::create([
            'table_id' => $validated['table_id'],
            'session_token' => Str::random(64),
            'status' => 'open',
            'customer_count' => $validated['customer_count'] ?? 1,
            'opened_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã mở phiên ăn thành công.',
            'data' => $session
        ], 201);
    }

    /**
     * Chuyển bàn: Chuyển phiên ăn sang bàn trống khác
     */
    public function moveTable(Request $request, int $id): JsonResponse
    {
        $session = DiningSession::where('id', $id)->where('status', 'open')->firstOrFail();
        $targetTableId = $request->input('target_table_id');

        $targetOccupied = DiningSession::where('table_id', $targetTableId)
            ->where('status', 'open')
            ->exists();

        if ($targetOccupied) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn đích hiện đang có khách ngồi, không thể chuyển sang.'
            ], 422);
        }

        $session->update(['table_id' => $targetTableId]);

        return response()->json([
            'success' => true,
            'message' => 'Đã chuyển bàn thành công.',
            'data' => $session->load('table')
        ]);
    }

    /**
     * Gộp bàn: Gộp toàn bộ món của phiên hiện tại sang phiên đích, sau đó đóng phiên hiện tại
     */
    public function mergeTable(Request $request, int $id): JsonResponse
    {
        $sourceSession = DiningSession::where('id', $id)->where('status', 'open')->firstOrFail();
        $targetSessionId = $request->input('target_session_id');

        $targetSession = DiningSession::where('id', $targetSessionId)
            ->where('status', 'open')
            ->firstOrFail();

        DB::transaction(function () use ($sourceSession, $targetSession) {
            // Chuyển toàn bộ orders và order_items sang phiên đích
            Order::where('session_id', $sourceSession->id)->update(['session_id' => $targetSession->id]);
            OrderItem::where('session_id', $sourceSession->id)->update(['session_id' => $targetSession->id]);

            // Đóng phiên nguồn
            $sourceSession->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã gộp bàn thành công.'
        ]);
    }

    /**
     * Đóng phiên ăn thủ công
     */
    public function closeSession(int $id): JsonResponse
    {
        $session = DiningSession::findOrFail($id);
        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đóng phiên ăn và giải phóng bàn.'
        ]);
    }
}
