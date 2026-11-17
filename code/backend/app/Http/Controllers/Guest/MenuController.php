<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dish;
use App\Models\ComboRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * Lấy toàn bộ danh mục và món ăn đang hoạt động
     */
    public function index(): JsonResponse
    {
        $categories = Category::with(['dishes' => function ($query) {
            $query->where('status', '!=', 'hidden')->orderBy('is_featured', 'desc');
        }])->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Tìm kiếm món ăn
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        $dishes = Dish::where('name', 'like', "%{$query}%")
            ->where('status', '!=', 'hidden')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dishes
        ]);
    }

    /**
     * Xem chi tiết món ăn
     */
    public function show(int $id): JsonResponse
    {
        $dish = Dish::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $dish
        ]);
    }

    /**
     * Gợi ý món ăn kèm (Combo) bằng thuật toán FP-Growth với cơ chế Fallback an toàn
     */
    public function suggestions(int $id): JsonResponse
    {
        // 1. Ưu tiên luật từ thuật toán FP-Growth
        $rules = ComboRule::with('consequentDish')
            ->where('antecedent_dish_id', $id)
            ->where('is_active', true)
            ->orderByDesc('confidence')
            ->limit(3)
            ->get();

        $suggestions = $rules->pluck('consequentDish')->filter();

        // 2. Nếu không đủ 3 món -> Fallback lấy các món cùng danh mục hoặc nổi bật
        if ($suggestions->count() < 3) {
            $currentDish = Dish::find($id);
            $excludeIds = $suggestions->pluck('id')->push($id)->all();

            $fallbacks = Dish::where('status', 'available')
                ->whereNotIn('id', $excludeIds)
                ->where('category_id', $currentDish?->category_id)
                ->limit(3 - $suggestions->count())
                ->get();

            $suggestions = $suggestions->concat($fallbacks);
        }

        // 3. Nếu vẫn chưa đủ -> Lấy các món bán chạy nhất quán
        if ($suggestions->count() < 3) {
            $excludeIds = $suggestions->pluck('id')->push($id)->all();
            $bestSellers = Dish::where('status', 'available')
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('is_featured')
                ->limit(3 - $suggestions->count())
                ->get();

            $suggestions = $suggestions->concat($bestSellers);
        }

        return response()->json([
            'success' => true,
            'data' => $suggestions->values()
        ]);
    }
}
