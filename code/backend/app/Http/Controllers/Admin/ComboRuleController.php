<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComboRule;
use App\Jobs\MineComboRulesJob;
use Illuminate\Http\JsonResponse;

class ComboRuleController extends Controller
{
    public function index(): JsonResponse
    {
        $rules = ComboRule::with(['antecedentDish', 'consequentDish'])
            ->orderByDesc('confidence')
            ->paginate(30);

        return response()->json(['success' => true, 'data' => $rules]);
    }

    public function reMine(): JsonResponse
    {
        MineComboRulesJob::dispatch();
        return response()->json([
            'success' => true,
            'message' => 'Đã kích hoạt job khai phá lại mô hình FP-Growth trong hàng đợi ngầm.'
        ]);
    }
}
