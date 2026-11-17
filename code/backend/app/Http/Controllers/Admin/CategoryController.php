<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Category::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);
        $cat = Category::create($validated);
        return response()->json(['success' => true, 'data' => $cat], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cat = Category::findOrFail($id);
        $cat->update($request->all());
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function destroy(int $id): JsonResponse
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa danh mục.']);
    }
}
