# 06 — API và giao diện

Base URL: `http://localhost:8000/api`. Ba nhóm client: **khách** (không login), **bếp/phục vụ/thu ngân** (Sanctum), **admin** (Sanctum).

## 1. Quy ước xác thực

| Client | Cơ chế | Header |
| --- | --- | --- |
| Khách | `session_token` sinh khi quét QR | `X-Session-Token: <64 hex>` |
| Nhân viên | Sanctum personal access token | `Authorization: Bearer <token>` |
| WebSocket khách | `session_token` qua auth endpoint | như trên |

Response envelope giống đề tài 1: `{ success, data, meta }` / `{ success, message, errors }`.

## 2. Endpoint khách (không login) — 14

| Method | Path | Mô tả |
| --- | --- | --- |
| GET | `/s/{tableToken}` | Quét QR: trả thông tin bàn + mở/lấy phiên + `session_token` |
| GET | `/s/menu` | Thực đơn đầy đủ (danh mục + món available) |
| GET | `/s/menu/dishes/{id}` | Chi tiết món |
| GET | `/s/menu/dishes/{id}/suggestions` | 3 món gợi ý kèm (combo) |
| GET | `/s/menu/search?q=` | Tìm món |
| POST | `/s/orders` | Gửi món xuống bếp |
| GET | `/s/orders` | Danh sách order của phiên |
| GET | `/s/orders/{id}` | Chi tiết 1 order |
| GET | `/s/items` | Tất cả món của phiên + trạng thái |
| POST | `/s/orders/{id}/items/{itemId}/cancel` | Khách xin hủy món (chỉ khi `pending`) |
| GET | `/s/bill` | Xem hóa đơn tạm tính (chưa phát hành) |
| POST | `/s/request-payment` | Yêu cầu thanh toán |
| GET | `/s/bill/pdf` | Tải PDF hóa đơn (sau khi đã thanh toán) |
| POST | `/s/feedback` | Đánh giá bữa ăn (tuỳ chọn) |

### 2.1. Ví dụ: quét QR

```http
GET /api/s/a1b2c3d4-e5f6-7890-abcd-ef1234567890
```

```json
// 200 — bàn đang trống, mở phiên mới
{ "success": true,
  "data": {
    "table": { "id": 12, "code": "B12", "area": "Tầng 1", "seats": 4 },
    "session": { "id": 501, "token": "9f8e...64hex", "status": "open" },
    "restaurant": { "name": "Quán Bếp Nhỏ", "vat_percent": 8, "service_charge_percent": 5 }
  } }
```

```json
// 200 — bàn đang có khách, trả lại phiên đang mở (KHÔNG tạo mới)
{ "success": true,
  "data": {
    "table": { "id": 12, "code": "B12" },
    "session": { "id": 498, "token": "...", "status": "open" },
    "warning": "Bàn này đang có khách. Nếu bạn là khách mới, hãy báo nhân viên."
  } }
```

### 2.2. Ví dụ: gửi món

```http
POST /api/s/orders
X-Session-Token: 9f8e...64hex
Content-Type: application/json
```

```json
{
  "items": [
    { "dish_id": 3, "quantity": 2, "note": "không hành" },
    { "dish_id": 15, "quantity": 1, "note": null }
  ],
  "note": "Ăn nhanh giúp em"
}
```

```json
// 201
{ "success": true,
  "data": {
    "id": 1203,
    "order_no": 2,
    "items": [
      { "id": 5501, "dish_name": "Phở bò", "quantity": 2, "status": "pending" },
      { "id": 5502, "dish_name": "Trà đá", "quantity": 1, "status": "pending" }
    ]
  } }
```

```json
// 422 — hết nguyên liệu
{ "success": false,
  "message": "Món Phở bò chỉ còn 1 phần",
  "errors": { "items.0.quantity": ["Vượt quá số phần còn lại"] } }
```

## 3. Endpoint nhân viên — 28

### 3.1. Auth (3)

| Method | Path | Quyền |
| --- | --- | --- |
| POST | `/staff/login` | public |
| POST | `/staff/logout` | auth |
| GET | `/staff/me` | auth |

### 3.2. Bếp (6)

| Method | Path | Quyền | Mô tả |
| --- | --- | --- | --- |
| GET | `/kitchen/queue?status=` | kitchen, admin | Hàng đợi món, sort FIFO |
| PATCH | `/kitchen/items/{id}/status` | kitchen, admin | Đổi trạng thái món |
| POST | `/kitchen/items/{id}/cancel` | kitchen, admin | Hủy món (bắt buộc lý do) |
| POST | `/kitchen/dishes/{id}/out-of-stock` | kitchen, admin | Báo hết món |
| POST | `/kitchen/dishes/{id}/back-in-stock` | kitchen, admin | Báo còn món |
| GET | `/kitchen/stats` | kitchen, admin | Số món chờ, món đang nấu, thời gian chờ TB |

### 3.3. Phục vụ (7)

| Method | Path | Quyền | Mô tả |
| --- | --- | --- | --- |
| GET | `/staff/tables` | waiter, cashier, admin | Sơ đồ bàn + trạng thái |
| GET | `/staff/sessions?status=open` | waiter, admin | Danh sách phiên đang mở |
| GET | `/staff/sessions/{id}` | waiter, cashier, admin | Chi tiết phiên: orders, items, bill tạm |
| POST | `/staff/sessions` | waiter, admin | Mở phiên thủ công (khách không quét QR) |
| POST | `/staff/sessions/{id}/move` | waiter, admin | Chuyển bàn |
| POST | `/staff/sessions/{id}/merge` | waiter, admin | Gộp bàn |
| POST | `/staff/sessions/{id}/close` | waiter, admin | Đóng phiên (sau khi thanh toán) |

### 3.4. Thu ngân (7)

| Method | Path | Quyền | Mô tả |
| --- | --- | --- | --- |
| GET | `/cashier/sessions/{id}/invoice` | cashier, admin | Xem hóa đơn nháp |
| POST | `/cashier/sessions/{id}/invoice` | cashier, admin | Phát hành hóa đơn |
| POST | `/cashier/invoices/{id}/discount` | cashier, admin | Áp giảm giá |
| POST | `/cashier/invoices/{id}/payments` | cashier, admin | Ghi nhận thanh toán |
| DELETE | `/cashier/payments/{id}` | admin | Xóa thanh toán ghi nhầm |
| GET | `/cashier/invoices/{id}/pdf` | cashier, admin | Xuất PDF |
| POST | `/cashier/invoices/{id}/cancel` | admin | Hủy hóa đơn |

### 3.5. Admin (5 — phần tối thiểu trong file này)

| Method | Path | Quyền |
| --- | --- | --- |
| GET/POST/PUT/DELETE | `/admin/dishes` | admin |
| GET/POST/PUT/DELETE | `/admin/categories` | admin |
| GET/POST/PUT/DELETE | `/admin/tables` | admin |
| GET/POST/PUT | `/admin/combo-rules` | admin |
| GET | `/admin/reports/revenue?from=&to=` | admin |

> Các CRUD còn lại (ingredients, dish_ingredients, staff, areas) theo cùng pattern — tổng cộng ~42 endpoint.

## 4. Màn hình Next.js (18)

### 4.1. Khách — mobile-first (8)

| # | Route | Nội dung chính |
| --- | --- | --- |
| 1 | `/s/[tableToken]` | Thực đơn có ảnh, danh mục dạng tab, giỏ hàng nổi ở dưới |
| 2 | `/s/[tableToken]/dish/[id]` | Chi tiết món + 3 món gợi ý kèm |
| 3 | `/s/[tableToken]/cart` | Giỏ: sửa số lượng, ghi chú từng món, nút “Gửi bếp” |
| 4 | `/s/[tableToken]/orders` | Các món đã gọi + trạng thái realtime + thời gian chờ |
| 5 | `/s/[tableToken]/bill` | Hóa đơn tạm tính, nút “Yêu cầu thanh toán” |
| 6 | `/s/[tableToken]/bill/paid` | Hóa đơn đã thanh toán + tải PDF |
| 7 | `/s/[tableToken]/search` | Tìm món |
| 8 | `/s/[tableToken]/feedback` | Đánh giá (tuỳ chọn) |

### 4.2. Bếp — tablet landscape (4)

| # | Route | Nội dung chính |
| --- | --- | --- |
| 9 | `/kitchen` | KDS: cột hoặc lưới món, đếm giờ, món quá hạn viền đỏ |
| 10 | `/kitchen/history` | Món đã xong hôm nay |
| 11 | `/kitchen/stock` | Danh sách món, bật/tắt nhanh “hết món” |
| 12 | `/kitchen/stats` | Số món chờ, thời gian chờ trung bình |

### 4.3. Phục vụ / thu ngân (4)

| # | Route | Nội dung chính |
| --- | --- | --- |
| 13 | `/staff/tables` | Sơ đồ bàn dạng lưới, màu theo trạng thái |
| 14 | `/staff/sessions/[id]` | Chi tiết phiên, nút chuyển bàn / gộp bàn |
| 15 | `/cashier/invoices` | Danh sách hóa đơn, filter |
| 16 | `/cashier/invoices/[id]` | Chi tiết, áp giảm giá, thu tiền, in PDF |

### 4.4. Admin (2 tối thiểu)

| # | Route | Nội dung chính |
| --- | --- | --- |
| 17 | `/admin/dishes` | Bảng món + form có upload ảnh + định lượng nguyên liệu |
| 18 | `/admin/combo-rules` | Bảng luật gợi ý, nút “Chạy lại FP-Growth”, cột confidence/lift |

## 5. Chi tiết màn hình KDS `/kitchen` (quan trọng nhất)

```
┌────────────────────────────────────────────────────────────────────────┐
│  BẾP — 14:32        [Chờ: 8]  [Đang nấu: 3]  [TB chờ: 6 phút]         │
├──────────────────┬──────────────────┬──────────────────┬───────────────┤
│ B12 · 14:26 · 6p │ B08 · 14:28 · 4p │ B15 · 14:30 · 2p │ B03 · 14:31   │
│ ──────────────── │ ──────────────── │ ──────────────── │ ───────────── │
│ Phở bò      ×2   │ Bún chả     ×1   │ Cơm gà      ×2   │ Nem rán    ×1 │
│  không hành      │                  │                  │               │
│ [Bắt đầu nấu]    │ [Bắt đầu nấu]    │ [Bắt đầu nấu]    │ [Bắt đầu nấu] │
│                  │                  │                  │               │
│ Trà đá      ×1   │ Chả giò     ×2   │ Canh cua    ×1   │               │
│ [Xong]           │ [Xong]           │ [Xong]           │               │
└──────────────────┴──────────────────┴──────────────────┴───────────────┘

⚠ Màu viền: xanh < 5 phút · vàng 5–10 phút · đỏ > 10 phút
🔔 Âm thanh khi có order mới
```

**Hành vi:**

- Món mới xuất hiện **không cần F5**, kèm hiệu ứng + tiếng.
- Mỗi card có nút đổi trạng thái kế tiếp (chỉ 1 nút, tránh bấm nhầm).
- Đồng hồ đếm phút tự cập nhật mỗi 30 giây.
- Sort mặc định theo `created_at` ASC (FIFO); có nút sắp theo thời gian chờ giảm dần.
- Nút “Hủy món” mở modal bắt buộc nhập lý do.

## 6. Chi tiết màn hình khách `/s/[tableToken]`

```
┌─────────────────────────────┐
│ Quán Bếp Nhỏ · Bàn B12      │
│ [Khai vị][Món chính][Uống]  │
├─────────────────────────────┤
│ ┌───────┐ Phở bò     60.000 │
│ │ ảnh   │ Bò tái, nạm...    │
│ └───────┘            [+]    │
│ ┌───────┐ Bún chả    45.000 │
│ │ ảnh   │ Chả nướng...      │
│ └───────┘            [+]    │
│                             │
│  ┌───────────────────────┐  │
│  │ 🛒 3 món · 145.000 đ  │  │
│  │     [Xem giỏ]         │  │
│  └───────────────────────┘  │
└─────────────────────────────┘
```

**Yêu cầu mobile-first:**

| Yếu tố | Ngưỡng |
| --- | --- |
| Cỡ chữ tối thiểu | 16px (tránh iOS tự zoom khi focus input) |
| Vùng bấm | ≥ 44×44 px |
| Ảnh món | lazy load, có placeholder |
| Giỏ hàng | thanh cố định dưới, không che nội dung |
| Thời gian tải | < 2 giây trên 4G (nén ảnh WebP, CDN hoặc resize server) |

## 7. Hiển thị trạng thái món cho khách

| Trạng thái | Icon | Nhãn | Màu |
| --- | --- | --- | --- |
| pending | ⏳ | “Đã gửi bếp” | xám |
| cooking | 🔥 | “Đang nấu” | cam |
| ready | 🔔 | “Sắp ra bàn” | xanh dương |
| served | ✅ | “Đã ra bàn” | xanh lá |
| cancelled | ✖ | “Đã hủy” | đỏ nhạt, gạch ngang |

## 8. Component dùng lại

| Component | Dùng ở |
| --- | --- |
| `DishCard` | Menu khách, gợi ý, admin |
| `CartBar` | Thanh giỏ hàng nổi |
| `StatusChip` | Trạng thái món (5 màu) |
| `TimerBadge` | KDS đếm phút, đổi màu theo ngưỡng |
| `OrderQueueCard` | KDS |
| `TableGrid` | Sơ đồ bàn phục vụ |
| `MoneyInput` | Form giá, giảm giá |
| `ConfirmDialog` | Hủy món, đóng phiên |
| `ConnectionBanner` | Cảnh báo mất WebSocket |

## 9. Xử lý lỗi phía frontend

| Tình huống | Hành vi |
| --- | --- |
| `session_token` hết hạn / phiên đã đóng | Modal “Phiên đã kết thúc” + nút quét lại QR |
| 422 hết món | Toast đỏ + tự refresh menu |
| 429 rate limit | Toast “Thao tác quá nhanh, thử lại sau” |
| Mất WebSocket | Banner vàng trên đầu trang, tự reconnect |
| 401 nhân viên | Redirect `/login` |
| 403 | Toast “Không có quyền” |

## 10. Rate limiting

```php
// routes/api.php
Route::middleware('throttle:guest-session')->group(function () {
    Route::post('/s/orders', [Guest\OrderController::class, 'store']);
});

// app/Providers/RouteServiceProvider.php
RateLimiter::for('guest-session', function (Request $request) {
    return Limit::perMinute(6)->by($request->header('X-Session-Token'));
});
```

Chặn khách bấm “Gửi bếp” liên tục tạo hàng loạt order rác.
