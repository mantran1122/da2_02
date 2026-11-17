<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\DiningSession;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| Định nghĩa các kênh WebSocket bảo mật cho Bếp, Khách hàng và Thu ngân
*/

// 1. Kênh Bếp (Chỉ nhân viên role kitchen hoặc admin)
Broadcast::channel('kitchen', function ($user) {
    return in_array($user->role, ['kitchen', 'admin', 'waiter']);
});

// 2. Kênh Phiên ăn của khách (Xác thực bằng X-Session-Token)
Broadcast::channel('table-session.{sessionToken}', function ($user, $sessionToken) {
    return DiningSession::where('session_token', $sessionToken)
        ->where('status', 'open')
        ->exists();
});

// 3. Kênh Quầy Thu ngân & Phục vụ (Nhận yêu cầu thanh toán & cập nhật bàn)
Broadcast::channel('cashier', function ($user) {
    return in_array($user->role, ['cashier', 'waiter', 'admin']);
});
