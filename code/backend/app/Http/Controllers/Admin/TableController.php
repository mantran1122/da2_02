<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Table::with('area')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'code' => 'required|string|unique:tables,code',
            'capacity' => 'required|integer|min:1',
        ]);
        $validated['qr_token'] = Str::uuid()->toString();

        $table = Table::create($validated);
        return response()->json(['success' => true, 'data' => $table], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $table = Table::findOrFail($id);
        $table->update($request->all());
        return response()->json(['success' => true, 'data' => $table]);
    }

    public function destroy(int $id): JsonResponse
    {
        Table::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa bàn.']);
    }
}
