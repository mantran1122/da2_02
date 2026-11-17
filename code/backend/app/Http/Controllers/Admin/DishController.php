<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DishController extends Controller
{
    public function index(): JsonResponse
    {
        $dishes = Dish::with(['category', 'dishIngredients.ingredient'])->paginate(20);
        return response()->json(['success' => true, 'data' => $dishes]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'price' => 'required|integer|min:0',
            'unit' => 'required|string|max:30',
            'estimated_prep_time' => 'required|integer|min:1',
            'is_featured' => 'nullable|boolean',
        ]);

        $dish = Dish::create($validated);
        return response()->json(['success' => true, 'data' => $dish], 201);
    }

    public function show(int $id): JsonResponse
    {
        $dish = Dish::with(['category', 'dishIngredients.ingredient'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $dish]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $dish = Dish::findOrFail($id);
        $dish->update($request->all());
        return response()->json(['success' => true, 'data' => $dish]);
    }

    public function destroy(int $id): JsonResponse
    {
        $dish = Dish::findOrFail($id);
        $dish->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa món thành công.']);
    }
}
