<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\DiningSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TableSessionController extends Controller
{
    /**
     * Quét mã QR: Trả lại phiên đang mở hoặc mở phiên mới nếu bàn đang trống
     */
    public function resolveSession(string $tableToken): JsonResponse
    {
        $table = Table::with(['area.restaurant'])->where('qr_token', $tableToken)->first();

        if (! $table || ! $table->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Mã QR bàn không hợp lệ hoặc bàn đang tạm ngưng phục vụ.'
            ], 404);
        }

        // Kiểm tra xem bàn có phiên nào đang mở không
        $session = DiningSession::where('table_id', $table->id)
            ->where('status', 'open')
            ->first();

        $warning = null;

        if (! $session) {
            // Mở phiên mới
            $session = DiningSession::create([
                'table_id' => $table->id,
                'session_token' => Str::random(64),
                'status' => 'open',
                'customer_count' => 1,
                'opened_at' => now(),
            ]);
        } else {
            $warning = 'Bàn này đang có khách ngồi. Nếu bạn là khách mới, vui lòng báo nhân viên phục vụ.';
        }

        $restaurant = $table->area?->restaurant;

        return response()->json([
            'success' => true,
            'data' => [
                'table' => [
                    'id' => $table->id,
                    'code' => $table->code,
                    'capacity' => $table->capacity,
                    'area_name' => $table->area?->name ?? 'Khu vực chính',
                ],
                'session' => [
                    'id' => $session->id,
                    'token' => $session->session_token,
                    'status' => $session->status,
                    'opened_at' => $session->opened_at,
                ],
                'restaurant' => [
                    'name' => $restaurant?->name ?? 'Nhà hàng Bếp Quán',
                    'vat_percent' => (float) ($restaurant?->vat_percent ?? 8.0),
                    'service_charge_percent' => (float) ($restaurant?->service_charge_percent ?? 0.0),
                ],
                'warning' => $warning,
            ]
        ]);
    }
}
