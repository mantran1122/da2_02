<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Table;
use Illuminate\Http\JsonResponse;

class TableMapController extends Controller
{
    /**
     * Sơ đồ bàn theo từng khu vực kèm trạng thái phiên ăn hiện tại
     */
    public function index(): JsonResponse
    {
        $areas = Area::with(['tables' => function ($query) {
            $query->with(['activeSession' => function ($sq) {
                $sq->withCount('orderItems');
            }]);
        }])->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $areas
        ]);
    }
}
