# 07 — Lộ trình triển khai 12 tuần

Giả định: 1 sinh viên, ~15–20 giờ/tuần. Nếu làm nhóm 2 người, nén xuống 8 tuần bằng cách chạy song song backend/frontend.

## 1. Tổng quan milestone

| Mốc | Tuần | Sản phẩm bàn giao |
| --- | --- | --- |
| M1 | 1–2 | Khảo sát, đặc tả, thiết kế DB, dựng khung repo + Reverb |
| M2 | 3–5 | Backend: CRUD, phiên ăn, order, state machine, tính bill + test |
| M3 | 6–8 | Frontend: menu khách + KDS + thu ngân — **mốc an toàn** |
| M4 | 9–10 | WebSocket realtime + FP-Growth gợi ý combo |
| M5 | 11 | Sơ đồ bàn, báo cáo, PDF, hoàn thiện |
| M6 | 12 | Đo lường, viết báo cáo, chuẩn bị bảo vệ |

## 2. Chi tiết từng tuần

### Tuần 1 — Khảo sát & đặc tả

**Việc làm**
- Đến 1–2 quán ăn bình dân quan sát quy trình gọi món (xin phép, ghi chú lại).
- Chụp lại mẫu phiếu order giấy, mẫu bill thật để thiết kế UI sát thực tế.
- Chốt phạm vi MVP với giảng viên.
- Vẽ use case diagram (5 actor) + activity diagram cho luồng QR → bếp → bill.

**Sản phẩm**: `01_TONG_QUAN_VA_PHAM_VI.md` bản cuối, 2 sơ đồ.

**Tiêu chí xong**: giải thích được trong 2 phút vì sao cần **phiên ăn** chứ không gắn order theo bàn.

### Tuần 2 — Thiết kế DB & dựng khung

**Việc làm**
- Viết 16 migration theo thứ tự ở file 03.
- Seeder: 1 nhà hàng, 3 khu vực, 20 bàn, 5 danh mục, 25 món, 8 nguyên liệu, định lượng cho 10 món.
- Seeder lịch sử: **giả lập 300 phiên ăn** với order ngẫu nhiên (dùng cho FP-Growth sau này).
- Cài Reverb: `composer require laravel/reverb && php artisan reverb:install`.
- Dựng Next.js + Tailwind + shadcn/ui.

**Sản phẩm**: `migrate:fresh --seed` chạy sạch; `php artisan reverb:start` khởi động được.

**Tiêu chí xong**: vào MySQL thấy 16 bảng có dữ liệu; terminal Reverb báo “server running”.

> **Đừng bỏ qua seeder lịch sử ở tuần này.** Nếu để đến tuần 10 mới nghĩ đến dữ liệu cho FP-Growth thì sẽ không kịp. Tạo sớm, dùng dần.

### Tuần 3 — Backend: auth + CRUD

**Việc làm**
- Sanctum cho `staff`, middleware `EnsureRole`.
- Model + quan hệ Eloquent đầy đủ (16 model).
- API admin: dishes, categories, ingredients, tables, areas (CRUD).
- API `GET /api/s/{tableToken}` — mở/lấy phiên (chưa cần WebSocket).

**Sản phẩm**: Postman chạy được login + CRUD + quét QR mở phiên.

**Tiêu chí xong**: quét cùng 1 QR hai lần → lần 2 trả lại phiên cũ, không tạo mới.

### Tuần 4 — Backend: order + state machine

**Việc làm**
- `OrderService::create()` với `lockForUpdate` và kiểm tra tồn kho.
- `StockService`: trừ kho, `maxPortions`, tự chuyển `out_of_stock`.
- `ItemStateMachine`: bảng chuyển trạng thái + validate + timestamps.
- API khách: gửi món, xem order, xem items.
- API bếp: queue, đổi trạng thái, hủy món.
- Viết unit test cho state machine (T08–T13 ở file 04).

**Sản phẩm**: test state machine pass 100%.

**Tiêu chí xong**: gọi món qua Postman → bếp đổi trạng thái đủ 4 bước → không nhảy cóc được.

### Tuần 5 — Backend: hóa đơn + thanh toán

**Việc làm**
- `InvoiceService::buildForSession()` — công thức ở file 04 mục 5.
- Unit test case 145.000 → 147.987 (test vàng).
- API thu ngân: phát hành bill, giảm giá, thanh toán.
- Logic đóng phiên + chuyển bàn + gộp bàn.
- API `POST /s/request-payment`.

**Sản phẩm**: Postman flow đầy đủ: mở phiên → gọi 3 lần → xem bill → thu tiền → đóng phiên.

**Tiêu chí xong**: tổng tiền khớp tính tay; đóng phiên khi còn món `cooking` bị chặn 409.

### Tuần 6 — Frontend: menu khách

**Việc làm**
- Layout mobile-first, giữ `session_token` trong `localStorage`.
- Trang menu: tab danh mục, `DishCard` có ảnh, nút thêm.
- Giỏ hàng client-side (zustand), `CartBar` nổi.
- Trang cart: sửa số lượng, ghi chú, gửi bếp.
- Trang orders: danh sách món (chưa realtime — chỉ refetch khi mở).

**Sản phẩm**: khách quét QR bằng điện thoại thật, gọi được món.

**Tiêu chí xong**: từ điện thoại thật (cùng wifi), gọi 2 món thành công, thấy trong DB.

### Tuần 7 — Frontend: KDS bếp

**Việc làm**
- Trang `/kitchen`: lưới card món, `TimerBadge` đổi màu theo phút.
- Nút đổi trạng thái (1 nút duy nhất, tránh nhầm).
- Modal hủy món có lý do.
- Trang `/kitchen/stock`: bật/tắt hết món.
- Auto-refetch mỗi 10 giây (tạm thời, tuần 9 thay bằng WebSocket).

**Sản phẩm**: bếp thao tác được toàn bộ vòng đời món trên tablet.

**Tiêu chí xong**: bếp đổi trạng thái → DB đúng → khách refetch thấy đúng.

### Tuần 8 — Frontend: thu ngân + sơ đồ bàn — **MỐC AN TOÀN**

**Việc làm**
- Trang `/cashier/invoices/[id]`: chi tiết bill, áp giảm giá, modal thu tiền.
- Trang `/staff/tables`: sơ đồ bàn dạng lưới, màu theo trạng thái.
- Trang `/staff/sessions/[id]`: chi tiết phiên, nút chuyển bàn.
- Xuất PDF hóa đơn (dompdf).

**Sản phẩm**: **hệ thống chạy end-to-end hoàn chỉnh, chưa có WebSocket và chưa có gợi ý combo**.

**Tiêu chí xong**: demo 5 phút được toàn bộ luồng với refetch thủ công. **Đây là điểm tựa an toàn** — nếu WebSocket/FP-Growth thất bại, đồ án vẫn đủ để bảo vệ.

### Tuần 9 — WebSocket realtime

**Việc làm**
- Định nghĩa 6 event class (file 05 mục 3).
- Cấu hình channel auth trong `routes/channels.php`.
- Client: `lib/echo.ts`, hook `useEcho` để tái sử dụng.
- Gắn listener vào KDS và trang orders của khách.
- Xử lý reconnect + banner mất kết nối.
- Đo latency 100 lần, ghi P50/P95.

**Sản phẩm**: order xuống bếp < 1 giây, không F5.

**Tiêu chí xong**: P95 latency < 500 ms; tắt wifi 10 giây bật lại vẫn đúng dữ liệu.

> **Đây là tuần rủi ro nhất.** Nếu Reverb gặp trục trặc khó gỡ sau 2 ngày, chuyển ngay sang polling 3 giây, giữ nguyên kiến trúc event (chỉ đổi transport), ghi rõ trong báo cáo là “đã thiết kế broadcast, triển khai fallback polling do hạn chế môi trường”.

### Tuần 10 — FP-Growth gợi ý combo

**Việc làm**
- Script Python `analysis/mine_rules.py` với `mlxtend`.
- Chạy trên dữ liệu seed 300 phiên, xem có bao nhiêu luật.
- Job Laravel `MineComboRules` gọi script định kỳ (hoặc chạy tay từ admin).
- `ComboRecommendationService` + endpoint suggestions.
- UI: hiện 3 món gợi ý dưới chi tiết món, kèm “X% khách gọi món này cũng gọi…”.
- Trang admin `/admin/combo-rules` hiển thị bảng luật + nút chạy lại.

**Sản phẩm**: gợi ý chạy được, có số liệu confidence/lift.

**Tiêu chí xong**: bấm chạy job → bảng `combo_rules` có dữ liệu → mở món A thấy gợi ý B.

### Tuần 11 — Đo lường, hoàn thiện, PDF

**Việc làm**
- Đánh giá Precision@3 so với 3 baseline (file 05 mục 10).
- Đo latency WebSocket chính thức, ghi bảng.
- Rà soát responsive: khách trên 375px, KDS trên tablet 1024×768.
- Bổ sung test case còn thiếu.
- Sửa các lỗi phát hiện khi test thủ công.

**Sản phẩm**: bộ số liệu đánh giá đầy đủ + hệ thống ổn định.

**Tiêu chí xong**: có đủ 2 bảng số liệu (latency, precision) để đưa vào báo cáo.

### Tuần 12 — Báo cáo & chuẩn bị bảo vệ

**Việc làm**
- Viết báo cáo theo template khoa (40–60 trang).
- Chụp ảnh màn hình, quay video demo 3–5 phút.
- Slide 15–20 trang.
- Viết `start-all.ps1` mở 4 terminal cùng lúc (API, Reverb, queue, frontend).
- Tập demo 5 lần liên tiếp không lỗi.
- Chuẩn bị trả lời ~18 câu hỏi ở file 09.

**Sản phẩm**: báo cáo, slide, video, repo tag `v1.0`, script khởi động.

## 3. Phân bổ thời gian

| Việc | Ước tính | Ghi chú |
| --- | --- | --- |
| Backend (tuần 3–5) | 45 giờ | Nhiều nhất |
| Frontend khách (tuần 6) | 20 giờ | Mobile-first tốn thời gian |
| Frontend KDS + thu ngân (tuần 7–8) | 30 giờ |  |
| WebSocket (tuần 9) | 20 giờ | **Rủi ro cao** |
| FP-Growth (tuần 10) | 15 giờ |  |
| Đo lường + báo cáo (tuần 11–12) | 30 giờ | Đừng để sát ngày |

## 4. Quản lý tiến độ

Dùng `TIEN_DO.md` ghi mỗi cuối tuần: đã xong gì, đang làm gì, vướng gì, tuần sau làm gì.

## 5. Nếu bị trễ — cắt gì trước?

Thứ tự cắt (từ trên xuống):

1. **Feedback khách** — không cần cho nghiệp vụ.
2. **Trang thống kê bếp** — nice-to-have.
3. **Gộp bàn** — hiếm dùng, có thể làm tay.
4. **Gợi ý combo** — nếu bỏ, chuyển sang “combo cấu hình tay”, vẫn đáp ứng yêu cầu đề tài ở mức cơ bản.
5. **WebSocket** — chuyển polling 3 giây, vẫn demo được nhưng mất điểm “realtime”.

**Tuyệt đối không cắt:** phiên ăn, vòng đời trạng thái món, tính hóa đơn, thanh toán. Đây là xương sống.

## 6. Rủi ro lịch trình cần theo dõi

| Tuần | Cảnh báo sớm |
| --- | --- |
| 4 | Nếu state machine chưa xong → tuần 5–8 sẽ dồn |
| 6 | Nếu menu khách chưa chạy trên điện thoại thật → responsive sẽ phát sinh về sau |
| 8 | **Nếu chưa end-to-end được → phải cắt bớt phạm vi ngay, không chờ** |
| 9 | Nếu Reverb không thông sau 2 ngày → chuyển polling |
| 10 | Nếu FP-Growth không ra luật (dữ liệu ít) → tăng seeder, hoặc dùng combo tay |
