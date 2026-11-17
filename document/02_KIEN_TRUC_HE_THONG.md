# 02 — Kiến trúc hệ thống

## 1. Kiến trúc tổng thể (4 khối)

```
┌───────────────────┐   ┌───────────────────┐   ┌───────────────────┐
│ KHÁCH (mobile)    │   │ BẾP (tablet KDS)  │   │ ADMIN (desktop)   │
│ Next.js /s/[token]│   │ Next.js /kitchen  │   │ Next.js /admin    │
└─────────┬─────────┘   └─────────┬─────────┘   └─────────┬─────────┘
          │  HTTPS + WSS          │                       │
          └───────────┬───────────┴───────────────────────┘
                      │
          ┌───────────▼────────────┐
          │  Laravel 11 API        │
          │  - Sanctum (staff)     │
          │  - session token(guest)│
          │  - Broadcasting (Reverb)│
          └───────────┬────────────┘
                      │
     ┌────────────────┼────────────────┐
     │                │                │
┌────▼─────┐   ┌──────▼──────┐   ┌─────▼──────────┐
│ MySQL 8  │   │ Redis       │   │ Storage        │
│ dữ liệu  │   │ queue+cache │   │ ảnh món, PDF   │
└──────────┘   └──────┬──────┘   └────────────────┘
                      │
          ┌───────────▼────────────┐
          │  Laravel Reverb        │
          │  WebSocket server      │
          │  (Pusher protocol)     │
          └────────────────────────┘

          ┌────────────────────────┐
          │ Queue worker:          │
          │ - FP-Growth job        │
          │ - sinh PDF             │
          └────────────────────────┘
```

### Vì sao Laravel Reverb thay vì Pusher/Ably?

| Phương án | Ưu | Nhược |
| --- | --- | --- |
| Pusher / Ably (cloud) | Không phải vận hành server WS | Có giới hạn gói free, phụ thuộc mạng, **không demo offline được** |
| **Laravel Reverb** (chọn) | Package first-party của Laravel 11+, chạy local, nói giao thức Pusher nên dùng được `laravel-echo` + `pusher-js` phía client | Phải chạy thêm 1 process |
| Tự viết WebSocket bằng Node `ws` | Kiểm soát hoàn toàn | Phải tự làm auth, channel, reconnect — mất 1–2 tuần |
| Polling mỗi 2 giây | Đơn giản nhất | Không phải “realtime”, tốn request, **mất điểm đề tài** |

Với đồ án, **Reverb là lựa chọn đúng**: nó là giải pháp chính thức của Laravel, chạy được offline, và vẫn dùng được thư viện client chuẩn.

## 2. Luồng end-to-end: khách gọi món → bếp nhận

```
Khách            Next.js           Laravel            Redis        Reverb        Bếp(KDS)
  │                 │                 │                 │            │              │
  │ quét QR bàn 12  │                 │                 │            │              │
  ├────────────────►│ GET /api/s/{tok}│                 │            │              │
  │                 ├────────────────►│ tìm bàn, tạo/   │            │            │
  │                 │                 │ lấy session mở  │            │              │
  │  session_token  │◄────────────────┤                 │            │              │
  │◄────────────────┤                 │                 │            │              │
  │                 │                 │                 │            │              │
  │ chọn 3 món      │                 │                 │            │              │
  │ bấm Gửi bếp     │                 │                 │            │              │
  ├────────────────►│ POST /orders    │                 │            │              │
  │                 ├────────────────►│ tạo order+items │            │              │
  │                 │                 │ trạng thái      │            │              │
  │                 │                 │ pending         │            │              │
  │                 │                 ├────────────────►│ broadcast  │              │
  │                 │                 │                 │ OrderCreated│             │
  │                 │                 │                 ├───────────►│              │
  │                 │                 │                 │            ├─────────────►│
  │                 │                 │                 │            │ hiện món mới │
  │                 │ 201 {order}     │                 │            │ (không F5)   │
  │                 │◄────────────────┤                 │            │              │
  │◄────────────────┤                 │                 │            │              │
  │                 │                 │                 │            │              │
  │                 │                 │                 │  bếp bấm    │              │
  │                 │                 │                 │  "Đang nấu" │              │
  │                 │                 │◄────────────────┼────────────┤              │
  │                 │                 │ PATCH /items/{id}/status       │              │
  │                 │                 │ cập nhật DB     │            │              │
  │                 │                 ├────────────────►│ broadcast  │              │
  │                 │                 │                 │ ItemStatus │              │
  │                 │                 │                 ├───────────►│              │
  │                 │◄────────────────┼─────────────────┼────────────┤              │
  │ thấy "Đang nấu" │                 │                 │            │              │
  │ (realtime)      │                 │                 │            │              │
```

**Điểm then chốt:** DB được ghi **trước** khi broadcast. Nếu broadcast thất bại, DB vẫn đúng và client refetch được. Đây là nguyên tắc “DB là nguồn sự thật”.

## 3. Kênh WebSocket (channel design)

| Channel | Ai subscribe | Sự kiện |
| --- | --- | --- |
| `private-kitchen.{restaurantId}` | Mọi tablet bếp | `OrderCreated`, `OrderItemStatusChanged`, `DishOutOfStock` |
| `private-session.{sessionToken}` | Chỉ khách của phiên đó | `OrderItemStatusChanged`, `InvoiceRequested` |
| `private-table.{tableId}` | Nhân viên phục vụ | `SessionOpened`, `SessionClosed`, `OrderCreated` |
| `private-admin.{restaurantId}` | Admin | `DishOutOfStock`, `ComboRuleUpdated` |

**Bảo mật channel:** dùng `Broadcast::channel()` trong `routes/channels.php` — kiểm tra quyền trước khi cho subscribe. Khách chỉ vào được channel của phiên mình (so khớp `sessionToken`).

```php
// routes/channels.php
Broadcast::channel('session.{token}', function ($user, string $token) {
    return DiningSession::where('token', $token)->where('status', 'open')->exists();
});

Broadcast::channel('kitchen.{restaurantId}', function ($user, int $restaurantId) {
    return $user && in_array($user->role, ['kitchen', 'admin'])
        && $user->restaurant_id === $restaurantId;
});
```

> **Lưu ý quan trọng về khách vãng lai:** khách không có tài khoản. Với Reverb, channel `private-*` cần auth endpoint. Có 2 cách: (a) dùng `PresenceChannel`/`PrivateChannel` với token phiên làm “user” tạm, hoặc (b) đơn giản hơn cho đồ án — dùng **channel theo token khó đoán** (token 32 byte random) và đặt tiền tố `private-` với một custom guard kiểm tra token. Cách (b) dễ làm hơn và vẫn đủ an toàn cho đồ án, ghi rõ trong báo cáo.

## 4. Sơ đồ module Laravel

```
app/
├── Models/
│   ├── Restaurant.php, Area.php, Table.php, QrCode.php
│   ├── DiningSession.php
│   ├── Category.php, Dish.php, DishImage.php
│   ├── Ingredient.php, DishIngredient.php
│   ├── Order.php, OrderItem.php
│   ├── Invoice.php, InvoiceItem.php, Payment.php
│   ├── ComboRule.php, ComboRuleItem.php
│   └── Staff.php
├── Http/Controllers/Api/
│   ├── Guest/
│   │   ├── SessionController.php      ← quét QR mở phiên
│   │   ├── MenuController.php         ← thực đơn
│   │   ├── CartController.php         ← (chỉ lưu client, BE ít dùng)
│   │   ├── OrderController.php        ← gọi món
│   │   └── InvoiceController.php      ← xem bill, yêu cầu thanh toán
│   ├── Kitchen/
│   │   ├── QueueController.php        ← hàng đợi món
│   │   ├── ItemStatusController.php   ← đổi trạng thái
│   │   └── StockController.php        ← báo hết món
│   ├── Staff/ (bàn, phiên, chuyển bàn)
│   └── Admin/ (CRUD đầy đủ)
├── Services/
│   ├── SessionService.php             ← mở/đóng phiên, chuyển bàn
│   ├── OrderService.php               ← tạo order, validate món còn hàng
│   ├── ItemStateMachine.php           ← luật chuyển trạng thái
│   ├── InvoiceService.php             ← tổng hợp, giảm giá, VAT
│   ├── StockService.php               ← trừ định lượng, phát hiện hết món
│   └── ComboRecommendationService.php ← đọc combo_rules
├── Jobs/
│   ├── MineComboRules.php             ← chạy FP-Growth định kỳ
│   └── GenerateInvoicePdf.php
├── Events/
│   ├── OrderCreated.php
│   ├── OrderItemStatusChanged.php
│   ├── DishOutOfStock.php
│   └── SessionOpened.php
├── Policies/ (TablePolicy, OrderPolicy, InvoicePolicy)
└── Enums/ (OrderItemStatus, SessionStatus, DishStatus, PaymentMethod)
```

## 5. Sơ đồ module Next.js

```
app/
├── s/[tableToken]/                    ← KHÁCH (không cần login)
│   ├── page.tsx                       ← thực đơn + giỏ
│   ├── cart/page.tsx                  ← xác nhận gửi bếp
│   ├── orders/page.tsx                ← theo dõi món realtime
│   ├── bill/page.tsx                  ← xem bill, yêu cầu thanh toán
│   └── layout.tsx                     ← giữ session_token, khởi tạo Echo
├── kitchen/                           ← BẾP
│   ├── page.tsx                       ← hàng đợi món, đếm giờ
│   └── stock/page.tsx                 ← báo hết món
├── staff/                             ← PHỤC VỤ
│   ├── tables/page.tsx                ← sơ đồ bàn
│   └── sessions/[id]/page.tsx         ← chi tiết phiên, chuyển bàn
├── admin/                             ← QUẢN LÝ
│   ├── dishes/  categories/  ingredients/
│   ├── tables/  qr/
│   ├── invoices/  reports/
│   └── combo-rules/
└── login/page.tsx                     ← nhân viên
```

**Điểm khác biệt so với đề tài 1:** phần khách **không có trang login**. Định danh dựa trên `session_token` lưu trong `localStorage` + URL. Đây là mô hình đúng của QR ordering.

## 6. Bảo mật và phân quyền

| Vấn đề | Cách xử lý |
| --- | --- |
| Nhân viên xác thực | Sanctum token, role trong `staff.role` |
| Khách không cần đăng nhập | `session_token` 32 byte random, lưu localStorage, gửi qua header `X-Session-Token` |
| Khách đoán token bàn khác | Token là 32 byte (2^256 khả năng) — không thể đoán; token bàn (QR) chỉ dùng để mở phiên, sau đó dùng token phiên |
| Khách xem order phiên khác | Mọi query order đều `where session_id = <từ token>` |
| Người lạ subscribe channel bếp | `Broadcast::channel` kiểm tra role |
| Bếp đổi trạng thái món của phiên khác | Không cần chặn — bếp có quyền trên toàn nhà hàng, nhưng ghi `audit_logs` ai đổi |
| Nhân viên sửa hóa đơn đã thanh toán | Chặn khi `invoice.status = paid` |
| Upload ảnh món độc hại | Validate MIME thật, giới hạn 5 MB, đổi tên UUID |
| Spam gọi món | Rate limit `POST /orders` theo session: tối đa 1 order/10 giây |
| QR bị chụp và dùng từ xa | Ngoài phạm vi MVP; ghi chú trong hạn chế — hướng mở rộng là kiểm tra IP wifi quán |

## 7. Quyết định kỹ thuật và lý do

| Quyết định | Lý do | Đánh đổi |
| --- | --- | --- |
| **Laravel Reverb** thay vì Pusher | Chạy local, demo offline, package chính thức | Phải chạy thêm 1 process |
| **Session theo phiên**, không theo bàn | QR in 1 lần dùng mãi; khách mới không thấy order cũ | Schema phức tạp hơn 1 bảng |
| `order_items` là đơn vị trạng thái (không phải `orders`) | 1 order có 5 món, mỗi món chín ở thời điểm khác nhau | Bếp phải thao tác trên item, không phải order |
| Snapshot giá vào `order_items.unit_price` | Thực đơn đổi giá không làm sai bill cũ | Trùng lặp dữ liệu có chủ đích |
| Redis cho queue + broadcast | Reverb dùng Redis làm backend pub/sub khi scale; queue cần cho job nặng | Thêm 1 service |
| FP-Growth chạy **offline job**, không tính realtime | Tính toán nặng (đọc toàn bộ lịch sử), không thể chạy mỗi lần khách mở menu | Gợi ý có độ trễ 1 ngày — chấp nhận được |
| Lưu `combo_rules` đã tính sẵn | Truy vấn gợi ý chỉ là `SELECT ... WHERE antecedent LIKE ...` — dưới 5 ms | Phải có job cập nhật |
| Gộp bill theo phiên, không tách theo người | Đơn giản hoá MVP | Nếu quán cần tách bill, phải mở rộng |

## 8. Cấu trúc thư mục repository

```
qr-order-restaurant/
├── backend/
│   ├── app/  database/  routes/  tests/
│   ├── .env.example
│   └── composer.json
├── frontend/
│   ├── app/  components/  lib/
│   ├── .env.local.example
│   └── package.json
├── analysis/                    ← script FP-Growth (Python, chạy offline)
│   ├── mine_rules.py
│   ├── requirements.txt
│   └── README.md
├── docker-compose.yml
└── docs/                        ← bộ file md này
```

## 9. Biến môi trường cần khai báo

**backend/.env**
```dotenv
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=qr_order
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=local-app
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

**frontend/.env.local**
```dotenv
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_REVERB_KEY=local-key
NEXT_PUBLIC_REVERB_HOST=127.0.0.1
NEXT_PUBLIC_REVERB_PORT=8080
NEXT_PUBLIC_REVERB_SCHEME=http
```

## 10. Lệnh chạy (4 process)

```bash
# Terminal 1 — API
php artisan serve

# Terminal 2 — WebSocket
php artisan reverb:start --debug

# Terminal 3 — queue worker (job FP-Growth, PDF)
php artisan queue:work --queue=default

# Terminal 4 — frontend
npm run dev
```

> Đây là điểm cần chuẩn bị kỹ khi demo: **phải bật đủ 4 process**. Viết sẵn file `start-all.ps1` để bấm 1 lần chạy hết.
