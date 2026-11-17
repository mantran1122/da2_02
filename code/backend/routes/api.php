<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guest;
use App\Http\Controllers\Kitchen;
use App\Http\Controllers\Staff;
use App\Http\Controllers\Cashier;
use App\Http\Controllers\Admin;

/*
|--------------------------------------------------------------------------
| 1. KHÁCH HÀNG (Guest QR Endpoints - 14)
|--------------------------------------------------------------------------
*/
// Quét mã QR mở hoặc lấy phiên ăn
Route::get('/s/{tableToken}', [Guest\TableSessionController::class, 'resolveSession']);

// Các route của khách yêu cầu header X-Session-Token
Route::prefix('s')->middleware('guest.session')->group(function () {
    // Thực đơn & Tìm kiếm & Chi tiết
    Route::get('/menu', [Guest\MenuController::class, 'index']);
    Route::get('/menu/search', [Guest\MenuController::class, 'search']);
    Route::get('/menu/dishes/{id}', [Guest\MenuController::class, 'show']);
    Route::get('/menu/dishes/{id}/suggestions', [Guest\MenuController::class, 'suggestions']);

    // Đơn hàng (giới hạn tần suất 6 lần/phút)
    Route::post('/orders', [Guest\OrderController::class, 'store'])->middleware('throttle:guest-session');
    Route::get('/orders', [Guest\OrderController::class, 'index']);
    Route::get('/orders/{id}', [Guest\OrderController::class, 'show']);
    Route::get('/items', [Guest\OrderController::class, 'items']);
    Route::post('/orders/{orderId}/items/{itemId}/cancel', [Guest\OrderController::class, 'cancelItem']);

    // Hóa đơn & Thanh toán & Feedback
    Route::get('/bill', [Guest\BillController::class, 'preview']);
    Route::post('/request-payment', [Guest\BillController::class, 'requestPayment']);
    Route::get('/bill/pdf', [Guest\BillController::class, 'downloadPdf']);
    Route::post('/feedback', [Guest\FeedbackController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| 2. NHÂN VIÊN - XÁC THỰC SANCTUM (Auth Endpoints - 3)
|--------------------------------------------------------------------------
*/
Route::post('/staff/login', [Staff\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/staff/logout', [Staff\AuthController::class, 'logout']);
    Route::get('/staff/me', [Staff\AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | 3. MÀN HÌNH BẾP (KDS Endpoints - 6)
    |--------------------------------------------------------------------------
    */
    Route::prefix('kitchen')->middleware('role:kitchen,admin')->group(function () {
        Route::get('/queue', [Kitchen\KdsQueueController::class, 'queue']);
        Route::patch('/items/{id}/status', [Kitchen\KdsQueueController::class, 'updateStatus']);
        Route::post('/items/{id}/cancel', [Kitchen\KdsQueueController::class, 'cancelItem']);
        Route::post('/dishes/{id}/out-of-stock', [Kitchen\StockController::class, 'markOutOfStock']);
        Route::post('/dishes/{id}/back-in-stock', [Kitchen\StockController::class, 'markBackInStock']);
        Route::get('/stats', [Kitchen\KdsQueueController::class, 'stats']);
    });

    /*
    |--------------------------------------------------------------------------
    | 4. PHỤC VỤ (Waiter Endpoints - 7)
    |--------------------------------------------------------------------------
    */
    Route::prefix('staff')->middleware('role:waiter,cashier,admin')->group(function () {
        Route::get('/tables', [Staff\TableMapController::class, 'index']);
        Route::get('/sessions', [Staff\SessionManagementController::class, 'index']);
        Route::get('/sessions/{id}', [Staff\SessionManagementController::class, 'show']);
        Route::post('/sessions', [Staff\SessionManagementController::class, 'storeManual']);
        Route::post('/sessions/{id}/move', [Staff\SessionManagementController::class, 'moveTable']);
        Route::post('/sessions/{id}/merge', [Staff\SessionManagementController::class, 'mergeTable']);
        Route::post('/sessions/{id}/close', [Staff\SessionManagementController::class, 'closeSession']);
    });

    /*
    |--------------------------------------------------------------------------
    | 5. THU NGÂN (Cashier Endpoints - 7)
    |--------------------------------------------------------------------------
    */
    Route::prefix('cashier')->middleware('role:cashier,admin')->group(function () {
        Route::get('/sessions/{id}/invoice', [Cashier\InvoiceController::class, 'preview']);
        Route::post('/sessions/{id}/invoice', [Cashier\InvoiceController::class, 'issue']);
        Route::post('/invoices/{id}/discount', [Cashier\InvoiceController::class, 'applyDiscount']);
        Route::post('/invoices/{id}/payments', [Cashier\PaymentController::class, 'store']);
        Route::delete('/payments/{id}', [Cashier\PaymentController::class, 'destroy'])->middleware('role:admin');
        Route::get('/invoices/{id}/pdf', [Cashier\InvoiceController::class, 'pdf']);
        Route::post('/invoices/{id}/cancel', [Cashier\InvoiceController::class, 'cancel'])->middleware('role:admin');
    });

    /*
    |--------------------------------------------------------------------------
    | 6. QUẢN TRỊ VIÊN (Admin CRUD & FP-Growth - 5+)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::apiResource('dishes', Admin\DishController::class);
        Route::apiResource('categories', Admin\CategoryController::class);
        Route::apiResource('tables', Admin\TableController::class);
        Route::get('combo-rules', [Admin\ComboRuleController::class, 'index']);
        Route::post('combo-rules/re-mine', [Admin\ComboRuleController::class, 'reMine']);
        Route::get('reports/revenue', [Admin\ReportController::class, 'revenue']);
    });
});
