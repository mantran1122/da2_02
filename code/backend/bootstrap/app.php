<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký alias middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'guest.session' => \App\Http\Middleware\GuestSessionAuth::class,
        ]);

        // Cấu hình Rate Limiter cho khách đặt món
        RateLimiter::for('guest-session', function (Request $request) {
            $token = $request->header('X-Session-Token') ?? $request->ip();
            return Limit::perMinute(6)->by($token)->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn thao tác quá nhanh. Vui lòng thử lại sau giây lát.'
                ], 429);
            });
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
