<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Báo cáo doanh thu và top món bán chạy
     */
    public function revenue(Request $request): JsonResponse
    {
        $fromDate = $request->query('from', now()->startOfMonth()->toDateString());
        $toDate = $request->query('to', now()->toDateString());

        $totalRevenue = Invoice::where('status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('total_amount');

        $totalOrders = Invoice::where('status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->count();

        $topDishes = OrderItem::with('dish')
            ->where('status', 'served')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->selectRaw('dish_id, SUM(quantity) as total_sold, SUM(quantity * unit_price) as total_sales')
            ->groupBy('dish_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'from' => $fromDate,
                'to' => $toDate,
                'total_revenue' => (int) $totalRevenue,
                'total_orders' => $totalOrders,
                'top_dishes' => $topDishes,
            ]
        ]);
    }
}
