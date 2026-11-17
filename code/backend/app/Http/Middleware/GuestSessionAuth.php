<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DiningSession;

class GuestSessionAuth
{
    /**
     * Xác thực phiên ăn của khách thông qua X-Session-Token
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Session-Token');

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu mã định danh phiên ăn (X-Session-Token). Vui lòng quét lại mã QR tại bàn.'
            ], 401);
        }

        $session = DiningSession::where('session_token', $token)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên ăn này đã kết thúc hoặc không tồn tại. Vui lòng quét lại mã QR.'
            ], 403);
        }

        // Đính kèm đối tượng session vào request
        $request->merge(['current_session' => $session]);
        $request->setUserResolver(fn () => null);

        return $next($request);
    }
}
