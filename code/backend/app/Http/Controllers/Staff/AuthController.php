<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Đăng nhập nhân viên bằng username & password -> Cấp Sanctum Token
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $staff = Staff::where('username', $validated['username'])->first();

        if (! $staff || ! Hash::check($validated['password'], $staff->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác.'
            ], 401);
        }

        if (! $staff->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị vô hiệu hóa.'
            ], 403);
        }

        $token = $staff->createToken('staff_token', [$staff->role])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => [
                'token' => $token,
                'staff' => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'username' => $staff->username,
                    'role' => $staff->role,
                ]
            ]
        ]);
    }

    /**
     * Đăng xuất nhân viên
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng xuất thành công.'
        ]);
    }

    /**
     * Lấy thông tin tài khoản đang đăng nhập
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }
}
