#!/usr/bin/env bash
# Script khởi chạy toàn bộ 4 tiến trình của dự án trên máy trạm hoặc máy phát triển

echo "=========================================================="
echo "  KHỞI ĐỘNG HỆ THỐNG QR ORDER & QUẢN LÝ BẾP (KDS)        "
echo "=========================================================="

# 1. Kiểm tra Docker nếu có
if command -v docker compose &> /dev/null; then
    echo "[INFO] Đã tìm thấy Docker Compose. Đang khởi chạy container..."
    docker compose up -d
    echo "[SUCCESS] Toàn bộ hệ thống đã chạy ngầm qua Docker!"
    echo "  - Backend API:    http://localhost:8000"
    echo "  - WebSocket:      ws://localhost:8080"
    echo "  - Frontend Web:   http://localhost:3000"
    exit 0
fi

# 2. Chạy native trực tiếp nếu không dùng Docker
echo "[INFO] Chạy trực tiếp native các dịch vụ..."

# Chuyển đến thư mục backend
cd backend

# Khởi chạy Laravel Reverb WebSocket
echo ">> [1/4] Khởi động Laravel Reverb (Cổng 8080)..."
php artisan reverb:start --port=8080 &
PID_REVERB=$!

# Khởi chạy Queue Worker
echo ">> [2/4] Khởi động Queue Worker..."
php artisan queue:work redis --sleep=3 --tries=3 &
PID_QUEUE=$!

# Khởi chạy Backend API
echo ">> [3/4] Khởi động Laravel API Server (Cổng 8000)..."
php artisan serve --port=8000 &
PID_API=$!

# Chuyển sang frontend
cd ../frontend
echo ">> [4/4] Khởi động Next.js Frontend (Cổng 3000)..."
npm run dev &
PID_FE=$!

echo "=========================================================="
echo "  HỆ THỐNG ĐÃ SẴN SÀNG:"
echo "  - Khách quét QR:  http://localhost:3000/s/a1b2c3d4-e5f6-7890-abcd-ef1234567890"
echo "  - Bếp KDS:        http://localhost:3000/kitchen"
echo "  - Sơ đồ bàn:      http://localhost:3000/staff/tables"
echo "  - Thu ngân POS:   http://localhost:3000/cashier/invoices"
echo "  - Quản trị Admin: http://localhost:3000/admin/dishes"
echo "=========================================================="
echo "Nhấn Ctrl+C để dừng toàn bộ hệ thống."

trap "kill $PID_REVERB $PID_QUEUE $PID_API $PID_FE; exit" INT TERM
wait
