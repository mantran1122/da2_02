@echo off
chcp 65001 > nul
echo ==========================================================
echo   KHỞI ĐỘNG HỆ THỐNG QR ORDER & QUẢN LÝ BẾP (KDS)
echo ==========================================================

REM Kiểm tra Docker Compose
docker compose version >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [INFO] Đang khởi chạy hệ thống bằng Docker Compose...
    docker compose up -d
    echo [SUCCESS] Đã khởi động toàn bộ dịch vụ!
    echo   - Backend API:    http://localhost:8000
    echo   - WebSocket:      ws://localhost:8080
    echo   - Frontend Web:   http://localhost:3000
    pause
    exit /b
)

echo [INFO] Khởi động native 4 cửa sổ command line...

start "Laravel Reverb" cmd /k "cd backend && php artisan reverb:start --port=8080"
start "Laravel Queue" cmd /k "cd backend && php artisan queue:work redis --sleep=3 --tries=3"
start "Laravel API" cmd /k "cd backend && php artisan serve --port=8000"
start "Next.js Frontend" cmd /k "cd frontend && npm run dev"

echo Đã mở 4 cửa sổ chạy nền.
pause
