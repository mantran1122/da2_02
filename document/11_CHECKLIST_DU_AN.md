# 11 — Checklist triển khai và nghiệm thu dự án theo lộ trình 12 tuần (12-Week Implementation & Delivery Checklist)

> **Dự án:** Hệ thống Gọi món tại Bàn (QR Order) và Quản lý Bếp Nhà hàng (KDS) tích hợp Gợi ý Thực đơn Thông minh (FP-Growth)  
> **Sinh viên thực hiện:** Hồ Văn Nhựt Cường | **Học phần:** Đồ án 2 (Năm học 2026–2027)  
> **Khối lượng chuẩn:** 12 tuần · ~15–20 giờ/tuần · Mốc an toàn tại Tuần 8 · 6 Milestones (M1 – M6)  
> **Tài liệu tham chiếu cốt lõi:** [01_TONG_QUAN_VA_PHAM_VI.md](01_TONG_QUAN_VA_PHAM_VI.md), [02_KIEN_TRUC_HE_THONG.md](02_KIEN_TRUC_HE_THONG.md), [03_THIET_KE_CSDL.md](03_THIET_KE_CSDL.md), [04_NGHIEP_VU_DON_HANG_VA_HOA_DON.md](04_NGHIEP_VU_DON_HANG_VA_HOA_DON.md), [05_WEBSOCKET_REALTIME_VA_GOI_Y_COMBO.md](05_WEBSOCKET_REALTIME_VA_GOI_Y_COMBO.md), [07_LO_TRINH_TRIEN_KHAI.md](07_LO_TRINH_TRIEN_KHAI.md), [08_KIEM_THU_VA_DANH_GIA.md](08_KIEM_THU_VA_DANH_GIA.md), [09_RUI_RO_VA_PHUONG_AN.md](09_RUI_RO_VA_PHUONG_AN.md), [10_THIET_KE_UI_UX.md](10_THIET_KE_UI_UX.md), [12_SLIDE_THUYET_TRINH_BAO_VE.md](12_SLIDE_THUYET_TRINH_BAO_VE.md), [13_BAO_CAO_DO_AN_TOT_NGHIEP.md](13_BAO_CAO_DO_AN_TOT_NGHIEP.md).

---

## ⚠️ ĐÁNH GIÁ THỰC TRẠNG & ĐIỂM NGHẼN HIỆN TẠI (REALITY CHECK)

> [!WARNING] **PHÂN BIỆT RÕ RÀNG GIỮA "MÃ NGUỒN BẢN THẢO" VÀ "HỆ THỐNG RUNTIME CHẠY THẬT":**
> - **Về mặt Hồ sơ Thiết kế & Bản thảo Mã nguồn:** Đã hoàn thành 100% cấu trúc (13 tài liệu đặc tả, 18 migrations, 16 models, 5 services, 17 controllers, Next.js 15 pages/components, Dockerfile, scripts phân tích).
> - **Về mặt Môi trường Thực thi (Runtime Execution):** **ĐANG TẠM DỪNG Ở TUẦN 2**. Máy host (Zorin OS) chưa cài đặt PHP 8.2, Node.js 20, Docker, MySQL 8, Redis 7. Do đó các lệnh thực thi (`composer install`, `npm install`, `php artisan migrate:fresh --seed`, `php artisan test`) **CHƯA ĐƯỢC CHẠY TRÊN HỆ THỐNG THẬT**.

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                              BẢNG ĐỐI CHIẾU TIẾN ĐỘ THỰC TẾ 12 TUẦN                                    │
├──────┬───────┬──────────────────────────────────────────┬──────────────────┬───────────────────────────┤
│ Mốc  │ Tuần  │ Nội dung công việc                       │ Trạng thái Code  │ Trạng thái Thực thi (Run) │
├──────┼───────┼──────────────────────────────────────────┼──────────────────┼───────────────────────────┤
│ M1   │ T1    │ Khảo sát, Đặc tả 5 nỗi đau, Use Case     │ ĐÃ XONG 100%     │ ĐÃ NGHIỆM THU ĐẶC TẢ      │
│      │ T2    │ CSDL 16 bảng, Migration, Docker Compose  │ ĐÃ VIẾT XONG     │ [!] CHỜ CÀI DOCKER/MYSQL  │
│ M2   │ T3    │ Auth Sanctum, Models, CRUD Danh mục      │ ĐÃ VIẾT XONG     │ [ ] Chờ chạy server       │
│      │ T4    │ OrderService, StockService, StateMachine │ ĐÃ VIẾT XONG     │ [ ] Chờ chạy PHPUnit test │
│      │ T5    │ InvoiceService (145k), Thu tiền, Đóng bàn│ ĐÃ VIẾT XONG     │ [ ] Chờ chạy PHPUnit test │
│ M3   │ T6    │ Frontend Mobile Thực khách (Next.js 15)  │ ĐÃ VIẾT XONG     │ [ ] Chờ test điện thoại   │
│      │ T7    │ Frontend KDS Bếp (Dark mode, FIFO)       │ ĐÃ VIẾT XONG     │ [ ] Chờ test máy tính bảng│
│      │ T8    │ Sơ đồ bàn, POS Thu ngân, Xuất PDF        │ ĐÃ VIẾT XONG     │ [ ] Chờ chạy kịch bản 5p  │
│ M4   │ T9    │ WebSocket Realtime (Laravel Reverb)      │ ĐÃ VIẾT XONG     │ [ ] Chờ chạy Reverb daemon│
│      │ T10   │ AI Combo Upselling (Thuật toán FP-Growth)│ ĐÃ VIẾT XONG     │ [ ] Chờ nạp Seeder 300 đ/h│
│ M5   │ T11   │ Benchmark P50/P95 & Đánh giá Precision@3 │ CÓ SCRIPT SIM    │ [ ] Chờ đo trên LAN thật  │
│ M6   │ T12   │ Báo cáo 5 chương, Slide, Demo & Bảo vệ   │ ĐÃ SOẠN THẢO     │ [ ] Chờ quay video & diễn tập│
└──────┴───────┴──────────────────────────────────────────┴──────────────────┴───────────────────────────┘
```

---

## GIAI ĐOẠN 1: MILESTONE M1 — KHẢO SÁT, THIẾT KẾ CSDL & MÔI TRƯỜNG (TUẦN 1 – TUẦN 2)

### Tuần 1 — Khảo sát thực tế, Phân tích nghiệp vụ & Chốt đặc tả
- **Mục tiêu tuần:** Làm rõ 5 nỗi đau vận hành thực tế tại quán ăn 15–30 bàn, vẽ sơ đồ Use Case (5 actors) và Activity Diagram cho luồng gọi món, thống nhất phạm vi MVP.
- **Sản phẩm bàn giao:** Tài liệu [01_TONG_QUAN_VA_PHAM_VI.md](01_TONG_QUAN_VA_PHAM_VI.md) và [02_KIEN_TRUC_HE_THONG.md](02_KIEN_TRUC_HE_THONG.md).
- **Checklist công việc:**
  - [x] Khảo sát quy trình ghi order giấy và thanh toán tại 1–2 quán ăn bình dân / quán phở.
  - [x] Xác định 5 Actor và ma trận phân quyền: `guest`, `kitchen`, `waiter`, `cashier`, `admin`.
  - [x] Chốt ranh giới phạm vi MVP: Không làm app mobile native, không tích hợp cổng thanh toán trực tuyến tiền thật, không quản lý kho chuyên sâu.
  - [x] Viết tài liệu [01_TONG_QUAN_VA_PHAM_VI.md](01_TONG_QUAN_VA_PHAM_VI.md) và phân tích 3 giả định nghiệp vụ cốt lõi (G1, G2, G3).
- **Tiêu chí hoàn thành (DoD):**
  - [x] ĐÃ ĐẠT: Giải thích được bản chất vì sao bắt buộc phải quản lý theo **Phiên ăn (`dining_sessions`)** thay vì gắn order trực tiếp vào bàn vật lý.

---

### Tuần 2 — Thiết kế Cơ sở dữ liệu, Dựng khung Repo & Môi trường chạy
- **Mục tiêu tuần:** Hiện thực hóa schema 16 bảng MySQL 8, cấu hình khóa ngoại, lập trình bộ Seeders thực tế và sinh sẵn 300 phiên ăn giả lập; dựng khung mã nguồn Laravel 11 và Next.js 15.
- **Sản phẩm bàn giao:** 16 tệp Migration; Bộ Seeders; `docker-compose.yml`; Script cài môi trường [setup_host_environment.sh](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/setup_host_environment.sh).
- **Checklist công việc:**
  - [x] Thiết kế chi tiết sơ đồ quan hệ CSDL 16 bảng trong [03_THIET_KE_CSDL.md](03_THIET_KE_CSDL.md).
  - [x] Viết đủ 18 tệp Migration chuẩn Laravel (`code/backend/database/migrations/`).
  - [x] Đánh chỉ mục Index tối ưu truy vấn: `qr_token`, `session_token`, `(status, created_at)` trên `order_items`.
  - [x] Viết Seeder thực đơn thực tế: 1 nhà hàng, 3 khu vực, 20 bàn, 5 danh mục, 25 món ăn có định lượng và ảnh SVG minh họa.
  - [x] Viết Seeder lịch sử 300 phiên ăn (`HistoricalOrdersSeeder.php`) phục vụ huấn luyện FP-Growth.
  - [x] Cấu hình Docker Compose đa container (`code/docker-compose.yml`) gồm 6 services.
  - [x] Khởi tạo Git repository (`git init`) và viết `.gitignore` chuẩn.
  - [x] Tạo sẵn các file cấu hình môi trường `.env` cho backend và `.env.local` cho frontend.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy `sudo ./setup_host_environment.sh` trên Zorin OS để cài đặt Docker/PHP/Node.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy `composer install` trong thư mục `code/backend`.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy `php artisan migrate:fresh --seed` trên cơ sở dữ liệu MySQL thật.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHƯA ĐẠT DO CHƯA CÀI MÔI TRƯỜNG: Chạy `php artisan migrate:fresh --seed` thành công 100% không báo lỗi foreign key; kiểm tra MySQL có đủ dữ liệu trên 16 bảng.

---

## GIAI ĐOẠN 2: MILESTONE M2 — BACKEND CORE: XÁC THỰC, PHIÊN ĂN, STATE MACHINE & HÓA ĐƠN (TUẦN 3 – TUẦN 5)

### Tuần 3 — Backend: Xác thực phân quyền, Khởi tạo Phiên ăn & Quản trị danh mục
- **Mục tiêu tuần:** Thiết lập Sanctum cho 4 role nhân viên; hoàn thiện Eloquent Models; hiện thực hóa API quét mã QR mở/tái sử dụng phiên ăn; viết API CRUD thực đơn admin.
- **Sản phẩm bàn giao:** 16 Eloquent Models; Middleware `EnsureRole` & `GuestSessionAuth`; Controllers xác thực và thực đơn.
- **Checklist công việc:**
  - [x] Viết 16 Eloquent Models với khai báo `$fillable`, `$casts` và các hàm quan hệ (`hasMany`, `belongsTo`, `belongsToMany`).
  - [x] Cài đặt và cấu hình Laravel Sanctum cấp token Bearer cho nhân viên (`Staff\AuthController.php`).
  - [x] Viết Middleware kiểm tra quyền theo vai trò: `App\Http\Middleware\EnsureRole.php`.
  - [x] Viết Middleware xác thực phiên khách: `App\Http\Middleware\GuestSessionAuth.php`.
  - [x] Lập trình API quét QR giải quyết phiên ăn: `GET /api/s/{tableToken}` (`TableSessionController.php`).
  - [x] Lập trình trọn bộ API CRUD Admin: Món ăn, Danh mục, Bàn ăn, Khu vực.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Khởi động backend và gửi request Postman test xác thực Bearer token 4 vai trò.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Quét cùng một mã QR bàn 2 lần liên tiếp để xác nhận trả về cùng 1 phiên ăn cũ.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ CHẠY SERVER: Quét cùng một mã QR bàn hai lần liên tiếp $\rightarrow$ Lần 2 trả về đúng ID phiên cũ, không tạo thêm bản ghi mới trong database.

---

### Tuần 4 — Backend: Đơn hàng, Quản lý kho định lượng & State Machine trạng thái món
- **Mục tiêu tuần:** Xây dựng lõi nghiệp vụ gọi món (`OrderService`) với cơ chế khóa dòng (`lockForUpdate`), trừ nguyên liệu tự động (`StockService`), và cỗ máy trạng thái 1 chiều (`ItemStateMachine`).
- **Sản phẩm bàn giao:** `OrderService.php`, `StockService.php`, `ItemStateMachine.php`; Unit tests kiểm thử State Machine.
- **Checklist công việc:**
  - [x] Xây dựng `StockService.php`: `getMaxPortions`, `deductForDish`, `restoreForDish`.
  - [x] Xây dựng `OrderService.php`: `DB::transaction` với `lockForUpdate`, snapshot `unit_price`.
  - [x] Xây dựng cỗ máy trạng thái `ItemStateMachine.php` chặn nhảy cóc và lưu timestamps.
  - [x] Lập trình API khách gọi món: `POST /api/s/orders`, `GET /api/s/orders`, `GET /api/s/items`.
  - [x] Lập trình API bếp KDS: `GET /kitchen/queue`, `PATCH /kitchen/items/{id}/status`, `POST /kitchen/items/{id}/cancel`.
  - [x] Viết Unit test: `ItemStateMachineTest.php` và `StockServiceTest.php`.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy lệnh `php artisan test --filter=ItemStateMachineTest` trên terminal.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy lệnh `php artisan test --filter=StockServiceTest` trên terminal.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ CHẠY TEST: Chạy `php artisan test --filter=ItemStateMachineTest` đạt 100% PASS; gọi thử món qua Postman chuyển đủ 4 bước trạng thái thành công.

---

### Tuần 5 — Backend: Tính toán hóa đơn, Nghiệp vụ thanh toán & Thao tác bàn
- **Mục tiêu tuần:** Hoàn thiện `InvoiceService` đáp ứng công thức chuẩn Golden Case 145.000 đ; API thu ngân thanh toán, đóng phiên ăn và giải phóng bàn; nghiệp vụ chuyển bàn và gộp bàn.
- **Sản phẩm bàn giao:** `InvoiceService.php`; Unit test tính tiền vàng; API thu ngân và quản lý phiên.
- **Checklist công việc:**
  - [x] Xây dựng `InvoiceService.php` hiện thực hóa công thức tính tiền $\text{subtotal} - \text{discount} + \text{service} + \text{VAT}$.
  - [x] Viết Unit test vàng [InvoiceServiceTest.php](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/backend/tests/Unit/InvoiceServiceTest.php).
  - [x] Lập trình API thu ngân: xuất hóa đơn, ghi nhận thanh toán, giải phóng bàn.
  - [x] Lập trình nghiệp vụ Chuyển bàn (`/staff/sessions/{id}/move`) và Gộp bàn (`/staff/sessions/{id}/merge`).
  - [x] Cấu hình Rate Limiter: `throttle:guest-session` tối đa 6 lần gửi đơn/phút.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy lệnh `php artisan test --filter=InvoiceServiceTest` trên terminal xác thực ca tính tiền 145.000 đ ra đúng 147.987 đ.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ CHẠY TEST: Chạy `php artisan test --filter=InvoiceServiceTest` đạt 100% PASS; chạy luồng Postman liên hoàn không xảy ra lỗi.

---

## GIAI ĐOẠN 3: MILESTONE M3 — FRONTEND: MENU KHÁCH, BẾP KDS & THU NGÂN — MỐC AN TOÀN (TUẦN 6 – TUẦN 8)

### Tuần 6 — Frontend: Giao diện Thực khách (Mobile-First Web App)
- **Mục tiêu tuần:** Xây dựng toàn bộ giao diện phía khách hàng tối ưu di động (360px – 430px), quản lý giỏ hàng phía trình duyệt bằng Zustand, kiểm thử trực tiếp trên điện thoại thật.
- **Sản phẩm bàn giao:** 5 Màn hình khách: `/s/[token]` (thực đơn), `/dish/[id]` (chi tiết), `/cart` (giỏ hàng), `/orders` (tiến độ), `/bill` (hóa đơn tạm).
- **Checklist công việc:**
  - [x] Thiết lập bố cục Mobile Shell với vùng bấm tối thiểu $44\times 44\text{ px}$.
  - [x] Xây dựng Store quản lý trạng thái: `sessionStore.ts` và `cartStore.ts`.
  - [x] Lập trình 5 trang giao diện khách hàng đầy đủ luồng.
  - [x] Tách component tái sử dụng `DishCard.tsx` vào thư mục `src/components/guest/`.
  - [x] Sinh 15 ảnh vector minh họa SVG món ăn chất lượng cao trong `public/images/dishes/`.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy `npm install` trong thư mục `code/frontend`.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Khởi động `npm run dev` và dùng camera điện thoại thật trong mạng Wi-Fi quét mã bàn B12 để kiểm tra hiển thị.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ THỬ NGHIỆM THIẾT BỊ: Mở camera điện thoại thật quét mã QR bàn B12 $\rightarrow$ Mở menu $\rightarrow$ Chọn 2 món $\rightarrow$ Gửi bếp thành công và kiểm tra dữ liệu xuất hiện trong database.

---

### Tuần 7 — Frontend: Màn hình Bếp KDS (Tablet Landscape)
- **Mục tiêu tuần:** Xây dựng màn hình KDS dành cho máy tính bảng đặt nằm ngang trong bếp; hiển thị danh sách đơn theo FIFO; huy hiệu đổi màu theo thời gian trôi qua; thao tác đổi trạng thái 1 chạm.
- **Sản phẩm bàn giao:** Màn hình KDS `/kitchen` và màn hình kho nhanh `/kitchen/stock`.
- **Checklist công việc:**
  - [x] Thiết kế giao diện khóa ngang (Landscape), Dark Mode (`bg-slate-900`) chống chói.
  - [x] Header hiển thị đồng hồ thời gian thực (`HH:mm:ss`), bộ đếm số lượng món.
  - [x] Bố cục lưới thẻ món ăn sắp xếp theo thứ tự FIFO (món gọi trước nằm trên cùng bên trái).
  - [x] Tách component KDS Ticket độc lập tại [KdsTicket.tsx](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/frontend/src/components/kitchen/KdsTicket.tsx) với bộ đổi màu viền (<5p xanh, 5-10p vàng, >10p đỏ nhấp nháy).
  - [x] Tạo file âm thanh chuông báo chuẩn `code/frontend/public/sounds/bell.wav` tần số 880Hz.
  - [x] Lập trình Màn hình Báo hết món nhanh (`/kitchen/stock`).
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Mở giao diện `/kitchen` trên màn hình phụ hoặc máy tính bảng và thử bấm nút chuyển trạng thái món.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ THỬ NGHIỆM THIẾT BỊ: Thao tác chuyển trạng thái món trên giao diện KDS $\rightarrow$ Database cập nhật chính xác.

---

### Tuần 8 — Frontend: Phục vụ, Thu ngân & Xuất PDF — ★ MỐC AN TOÀN TOÀN HỆ THỐNG
- **Mục tiêu tuần:** Hoàn thành Sơ đồ bàn cho phục vụ, POS thanh toán cho thu ngân, xuất hóa đơn in ấn; chạy thông suốt luồng nghiệp vụ End-to-End từ khách quét mã đến thu tiền đóng bàn.
- **Sản phẩm bàn giao:** Sơ đồ bàn `/staff/tables`; POS Thu ngân `/cashier/invoices/[id]`; Bản in hóa đơn PDF.
- **Checklist công việc:**
  - [x] Lập trình Màn hình Sơ đồ bàn trực quan (`/staff/tables`) phân màu theo trạng thái.
  - [x] Tách component `TableBadge.tsx` và `ModalConfirm.tsx` vào thư mục components.
  - [x] Lập trình Màn hình POS Thu ngân (`/cashier/invoices/[id]`) kèm ô chiết khấu và phương thức thanh toán.
  - [x] Tạo template xuất hóa đơn PDF [pdf/invoice.blade.php](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/backend/resources/views/pdf/invoice.blade.php).
  - [x] Tạo cổng điều hướng tổng hợp [src/app/page.tsx](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/frontend/src/app/page.tsx).
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Diễn tập chạy thử chu trình khép kín 5 phút trên trình duyệt: Quét QR $\rightarrow$ Gọi món $\rightarrow$ Bếp nhận nấu $\rightarrow$ Bếp nấu xong $\rightarrow$ Thu ngân thu tiền và đóng bàn.
- **Tiêu chí hoàn thành (DoD) — MỐC AN TOÀN ĐỒ ÁN:**
  - [ ] CHỜ DIỄN TẬP LIVE: Chạy thành công chu trình 5 phút mà không cần bật WebSocket (dùng cơ chế refetch).

---

## GIAI ĐOẠN 4: MILESTONE M4 — TÍNH NĂNG NÂNG CAO: REALTIME WEBSOCKET & GỢI Ý COMBO AI (TUẦN 9 – TUẦN 10)

### Tuần 9 — Tích hợp Laravel Reverb WebSocket: Đồng bộ Thời gian thực
- **Mục tiêu tuần:** Kết nối Laravel Reverb với Next.js Echo; phát sóng 6 sự kiện realtime; loại bỏ hoàn toàn cơ chế F5/polling; chuông báo âm thanh KDS; xử lý rớt mạng.
- **Sản phẩm bàn giao:** 6 Broadcast Events; Client Singleton `echo.ts`; Component `ConnectionBanner.tsx`.
- **Checklist công việc:**
  - [x] Viết 6 Broadcast Event classes (`OrderCreated`, `ItemStatusChanged`, `ItemCancelled`, `DishOutOfStock`, `DishBackInStock`, `PaymentRequested`).
  - [x] Cấu hình Client Singleton `echo.ts` kết nối Pusher-JS tới cổng Reverb 8080.
  - [x] Tạo component cảnh báo mất mạng `ConnectionBanner.tsx` tự động kết nối lại sau 3 giây.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy daemon Reverb: `php artisan reverb:start --port=8080`.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Mở đồng thời 2 tab trình duyệt (Khách và Bếp KDS), bấm gửi món trên tab Khách và quan sát tab Bếp nhảy thẻ kèm phát tiếng chuông trong $<1$ giây.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ CHẠY DAEMON REVERB: Khách gửi món $\rightarrow$ Màn hình KDS nhận được đơn hàng và phát chuông trong $<1$ giây không cần reload trang.

---

### Tuần 10 — Khai phá Luật Kết hợp Thực đơn bằng FP-Growth (AI Combo Upselling)
- **Mục tiêu tuần:** Hiện thực hóa thuật toán khai phá luật kết hợp FP-Growth trên dữ liệu lịch sử gọi món; sinh luật gợi ý món kèm $A \rightarrow B$; tích hợp khối gợi ý combo vào chi tiết món ăn.
- **Sản phẩm bàn giao:** `FpGrowthService.php`, `MineComboRulesJob.php`, lệnh `combo:mine-rules`; Giao diện quản trị luật `/admin/combo-rules`.
- **Checklist công việc:**
  - [x] Xây dựng dịch vụ khai phá luật kết hợp [FpGrowthService.php](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/backend/app/Services/FpGrowthService.php) với Support $\ge 0.05$, Confidence $\ge 0.30$, Lift $> 1.0$.
  - [x] Đóng gói Job xử lý ngầm qua hàng đợi Redis: `MineComboRulesJob.php` và lệnh Console `combo:mine-rules`.
  - [x] Lập trình API gợi ý món kèm với cơ chế Fallback 3 lớp (FP-Growth $\rightarrow$ Manual $\rightarrow$ Top sellers).
  - [x] Lập trình Giao diện Quản trị luật [src/app/(admin)/admin/combo-rules/page.tsx](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/frontend/src/app/(admin)/admin/combo-rules/page.tsx).
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy lệnh `php artisan combo:mine-rules` để nạp luật từ 300 phiên ăn mẫu vào bảng `combo_rules`.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Mở chi tiết món Phở bò trên app khách kiểm tra hiển thị gợi ý combo Nem rán và Trà đá.
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ CHẠY LỆNH FP-GROWTH: Bảng `combo_rules` có ít nhất 10 luật hợp lệ và app khách hiển thị đúng gợi ý tương ứng.

---

## GIAI ĐOẠN 5: MILESTONE M5 — ĐO LƯỜNG, ĐÁNH GIÁ THỰC NGHIỆM & TỐI ƯU HÓA (TUẦN 11)

### Tuần 11 — Benchmark Độ trễ, Đánh giá Mô hình FP-Growth & Tối ưu hóa Toàn diện
- **Mục tiêu tuần:** Chạy kịch bản đo kiểm thực nghiệm 100 lần lấy phân vị độ trễ P50/P95; đánh giá Precision@3 của FP-Growth trên tập test 20%; kiểm tra responsive trên nhiều thiết bị thật.
- **Sản phẩm bàn giao:** Script đo kiểm `benchmark_latency.py` và `evaluate_fp_growth.py`; 2 Bảng số liệu thực nghiệm.
- **Checklist công việc:**
  - [x] Xây dựng script benchmark độ trễ [scripts/benchmark_latency.py](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/scripts/benchmark_latency.py).
  - [x] Xây dựng script đánh giá thuật toán gợi ý [scripts/evaluate_fp_growth.py](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/scripts/evaluate_fp_growth.py).
  - [x] Chạy thử nghiệm mô phỏng và thu thập số liệu sơ bộ: P50 = 136.94 ms, P95 = 184.08 ms, Precision@3 = 31.3% – 36.5%.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Chạy script `benchmark_latency.py` trực tiếp bắn request vào backend Laravel đang chạy thật để lấy số liệu thực nghiệm chính thức.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Kiểm tra responsive thực tế trên ít nhất 2 kích thước màn hình điện thoại (iPhone/Android).
- **Tiêu chí hoàn thành (DoD):**
  - [ ] CHỜ CHẠY BENCHMARK TRÊN LIVE BACKEND: Có log đo kiểm thực tế từ server live để cập nhật vào báo cáo đồ án.

---

## GIAI ĐOẠN 6: MILESTONE M6 — HỒ SƠ BÁO CÁO, KỊCH BẢN DEMO & BẢO VỆ ĐỒ ÁN (TUẦN 12)

### Tuần 12 — Soạn thảo Báo cáo, Slide thuyết trình & Tổng duyệt Demo
- **Mục tiêu tuần:** Hoàn thành cuốn Báo cáo đồ án 5 chương theo chuẩn khoa; hoàn thiện Slide thuyết trình 15 trang; in ấn mã QR plastic; đóng gói script chạy 1 chạm và diễn tập demo.
- **Sản phẩm bàn giao:** Báo cáo khoa học [13_BAO_CAO_DO_AN_TOT_NGHIEP.md](13_BAO_CAO_DO_AN_TOT_NGHIEP.md); Slide thuyết trình [12_SLIDE_THUYET_TRINH_BAO_VE.md](12_SLIDE_THUYET_TRINH_BAO_VE.md); Trang in mã QR [print_qr_cards.html](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/frontend/public/print_qr_cards.html); Script khởi động `start-all.sh`.
- **Checklist công việc:**
  - [x] Soạn thảo toàn văn Cuốn Báo cáo Đồ án tốt nghiệp 5 chương học thuật tại [13_BAO_CAO_DO_AN_TOT_NGHIEP.md](13_BAO_CAO_DO_AN_TOT_NGHIEP.md).
  - [x] Soạn thảo bộ 15 Slide thuyết trình chuẩn 15 phút tại [12_SLIDE_THUYET_TRINH_BAO_VE.md](12_SLIDE_THUYET_TRINH_BAO_VE.md) kèm Speaker Notes.
  - [x] Thiết kế trang in mã QR chuẩn khổ A4 ép plastic tại [code/frontend/public/print_qr_cards.html](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/frontend/public/print_qr_cards.html).
  - [x] Đóng gói Script khởi chạy tự động [code/start-all.sh](file:///home/mantran1507/Desktop/02_qr_order_quan_ly_bep/code/start-all.sh) và `start-all.bat`.
  - [x] Tổng hợp bộ 18 câu hỏi phản biện hội đồng trong [09_RUI_RO_VA_PHUONG_AN.md](09_RUI_RO_VA_PHUONG_AN.md).
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Xuất file `13_BAO_CAO_DO_AN_TOT_NGHIEP.md` sang định dạng Word/PDF đóng bìa cứng nộp khoa.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Quay 1 video demo 3 phút Full HD (1080p) lưu trên máy tính phòng sự cố phòng bảo vệ rớt mạng.
  - [ ] **[CẦN THỰC HIỆN TRÊN MÁY THẬT]:** Diễn tập trực tiếp kịch bản demo 5 phút trên 3 thiết bị thật (Điện thoại, Tablet, Laptop) 5 lần liên tục.
- **Tiêu chí hoàn thành (DoD) — NGHIỆM THU CUỐI CÙNG:**
  - [ ] CHỜ BUỔI BẢO VỆ: Bản in báo cáo đã nộp; Repo gắn tag release `v1.0`; diễn tập demo trơn tru sẵn sàng bảo vệ trước Hội đồng chấm thi.
