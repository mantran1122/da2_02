# 12 — Đề cương 15 Slide thuyết trình bảo vệ đồ án (Defense Presentation Slides)

Tài liệu này chuẩn hóa toàn bộ nội dung, bố cục trực quan, ghi chú người thuyết trình (Speaker Notes) và thời lượng chuẩn cho 15 Slide bảo vệ đồ án trước Hội đồng chấm thi.

---

## Cấu trúc thời lượng (Tổng cộng: 15 phút)

- **Phần 1: Giới thiệu & Đặt vấn đề (3 phút)**: Slide 1 $\rightarrow$ 3
- **Phần 2: Kiến trúc & Nghiệp vụ cốt lõi (5 phút)**: Slide 4 $\rightarrow$ 8
- **Phần 3: Thuật toán AI FP-Growth & Realtime WebSocket (3 phút)**: Slide 9 $\rightarrow$ 11
- **Phần 4: Demo thực tế & Đánh giá kết quả (3 phút)**: Slide 12 $\rightarrow$ 14
- **Phần 5: Kết luận & Hướng phát triển (1 phút)**: Slide 15

---

## Slide 1: Trang tiêu đề (Title Slide)
- **Tiêu đề lớn**: HỆ THỐNG GỌI MÓN TẠI BÀN (QR ORDER) VÀ QUẢN LÝ BẾP (KDS) TÍCH HỢP GỢI Ý THỰC ĐƠN THÔNG MINH
- **Sinh viên thực hiện**: Hồ Văn Nhựt Cường
- **Học phần**: Đồ án 2 — Khoa Công nghệ Thông tin (Năm học 2026–2027)
- **Công nghệ chính**: Laravel 11 API · Laravel Reverb (WebSocket) · Next.js 15 · MySQL 8 · Redis · FP-Growth
- **Speaker Note (30s)**:
  > *"Kính thưa quý thầy cô trong Hội đồng, em xin phép trình bày đề tài đồ án tốt nghiệp: Hệ thống Gọi món tại bàn và Quản lý bếp nhà hàng KDS có tích hợp khai phá luật gợi ý combo bằng thuật toán FP-Growth..."*

---

## Slide 2: Đặt vấn đề & Nỗi đau thực tế (Problem Statement)
- **Bối cảnh**: Các quán ăn và nhà hàng quy mô 15–30 bàn đang vận hành thủ công bằng giấy ghi phiếu.
- **5 Nỗi đau vận hành lớn**:
  1. *Gọi món chậm*: Khách chờ phục vụ 5–10 phút vào giờ cao điểm.
  2. *Sai sót do chuyển tay*: Chữ viết tay khó đọc, món lên nhầm bàn.
  3. *Bếp tắc nghẽn*: Phiếu giấy xếp chồng, không quản lý được thứ tự FIFO (món nào vào trước nấu trước).
  4. *Khách sốt ruột*: Khách hỏi "món tôi đâu rồi", nhân viên phải chạy vào bếp hỏi lại.
  5. *Lãng phí thời gian khi hết món*: Khách gọi xong bếp mới báo hết nguyên liệu.
- **Speaker Note (60s)**:
  > *"Mục tiêu của đề tài là số hóa luồng gọi món end-to-end: khách tự chọn món trên điện thoại, order chảy thẳng xuống màn hình bếp KDS trong chưa đầy 1 giây, và trạng thái nấu được đồng bộ ngược lại máy khách theo thời gian thực."*

---

## Slide 3: Mục tiêu đề tài & Phạm vi MVP (Objectives & Scope)
- **Mục tiêu định lượng**:
  - Thời gian gọi món của khách $< 60$ giây.
  - Độ trễ hiển thị từ lúc khách bấm "Gửi" đến màn hình bếp $< 1$ giây ($P95 < 800\text{ ms}$).
  - Gợi ý món kèm (combo) đạt độ chính xác $\text{Precision@3} \ge 35\%$.
- **4 Khối chức năng phục vụ 4 đối tượng**:
  - Khách hàng (Mobile Web không cần cài app).
  - Bếp KDS (Tablet Landscape cảm ứng 1 chạm).
  - Phục vụ & Thu ngân (Sơ đồ bàn & POS tính tiền).
  - Quản trị Admin (Định lượng nguyên liệu & Huấn luyện FP-Growth).

---

## Slide 4: Kiến trúc hệ thống tổng thể (System Architecture)
- **Sơ đồ 4 khối kỹ thuật**:
  - *Client Layer*: Next.js 15 (Tailwind CSS, Zustand, Echo + Pusher JS).
  - *API & Realtime Layer*: Laravel 11 REST API, Sanctum Auth, Laravel Reverb WebSocket Server cổng 8080.
  - *Data & Cache Layer*: MySQL 8 (16 bảng dữ liệu), Redis 7 (Cache, Queue, Pub/Sub).
  - *Job Processing Layer*: Queue Worker xử lý ngầm thuật toán FP-Growth và xuất PDF.
- **Quyết định công nghệ then chốt**:
  - Vì sao dùng **Laravel Reverb**: Gói first-party của Laravel 11, chuẩn Pusher, chạy nội bộ offline không phụ thuộc internet như Pusher/Ably cloud.

---

## Slide 5: Quyết định thiết kế then chốt — Bảng Phiên ăn (`dining_sessions`)
- **Vấn đề**: Vì sao không gắn Order trực tiếp vào Bàn (`tables`)?
- **Giải pháp thiết kế**:
  - Bàn là thực thể **vật lý cố định**. Khách ăn là thực thể **thay đổi theo thời gian**.
  - Tách bảng `dining_sessions`: Mỗi lượt khách ngồi sinh 1 phiên ăn kèm token ngẫu nhiên 64 ký tự.
  - Khi khách thanh toán xong $\rightarrow$ Phiên đóng. Khách tiếp theo ngồi vào sẽ mở phiên mới, hoàn toàn không thấy order cũ.
- **Lợi ích thực tế**:
  - **Mã QR in sẵn trên bàn dùng vĩnh viễn**, không bao giờ phải in lại mỗi khi đổi lượt khách.

---

## Slide 6: Thiết kế CSDL (Database Schema 16 bảng)
- **Các cụm bảng nghiệp vụ chính**:
  - *Nhà hàng & Bàn*: `restaurants`, `areas`, `tables`, `staff`.
  - *Thực đơn & Kho tồn*: `categories`, `dishes`, `ingredients`, `dish_ingredients`.
  - *Đơn hàng & Vòng đời*: `dining_sessions`, `orders`, `order_items`.
  - *Hóa đơn & Thanh toán*: `invoices`, `invoice_items`, `payments`.
  - *Khuyến nghị & Đánh giá*: `combo_rules`, `feedbacks`.
- **Tối ưu hóa hiệu năng**:
  - Composite Index trên `(status, created_at)` phục vụ truy vấn hàng đợi KDS trong vài mili-giây.
  - `lockForUpdate` chống race condition khi 2 khách cùng đặt món cuối cùng.

---

## Slide 7: Nghiệp vụ Đơn hàng & State Machine trạng thái món
- **Quy tắc chuyển trạng thái 1 chiều (State Machine)**:
  $$\text{pending} \longrightarrow \text{cooking} \longrightarrow \text{ready} \longrightarrow \text{served}$$
  - Chặn đứng mọi hành vi nhảy cóc (VD: không được nhảy từ `pending` sang `served`).
  - Mỗi bước nhảy đều ghi nhận timestamp chính xác (`cooking_started_at`, `ready_at`, `served_at`).
- **Nghiệp vụ Hủy món an toàn**:
  - Bắt buộc nhập lý do hủy.
  - Tự động hoàn trả nguyên liệu về kho tồn kho.
  - Khách chỉ được hủy khi món còn ở trạng thái `pending`.

---

## Slide 8: Công thức tính toán Hóa đơn & Test vàng Golden Case
- **Công thức tính tiền chuẩn**:
  $$\text{Subtotal} - \text{Discount} + \text{Phí phục vụ} + \text{Thuế VAT} = \text{Tổng tiền}$$
- **Quy tắc**: Thuế VAT được tính trên tổng sau khi đã cộng phí phục vụ. Các món bị hủy (`cancelled`) bị loại bỏ hoàn toàn.
- **Golden Case 145.000 đ**:
  - Tiền món: $145.000\text{ đ}$, Giảm giá: $14.500\text{ đ}$, Phí PV 5%: $6.525\text{ đ}$, VAT 8%: $10.962\text{ đ}$.
  - Tổng thanh toán: **$147.987\text{ đ}$** (Unit test PHPUnit pass 100%).

---

## Slide 9: Giải pháp Realtime với Laravel Reverb (WebSocket)
- **6 Sự kiện phát sóng thời gian thực**:
  - `OrderCreated` $\rightarrow$ Bếp nhận ngay, chuông kêu.
  - `ItemStatusChanged` $\rightarrow$ Điện thoại khách đổi màu món.
  - `ItemCancelled` $\rightarrow$ Món trên app khách gạch ngang đỏ.
  - `DishOutOfStock` $\rightarrow$ Menu tự chuyển sang "Hết món".
  - `PaymentRequested` $\rightarrow$ Quầy thu ngân nhấp nháy chuông.
- **Nguyên tắc "DB là nguồn sự thật"**:
  - Ghi Database xong mới broadcast event. Nếu mất mạng, client tự reconnect và refetch dữ liệu.

---

## Slide 10: Thuật toán Gợi ý Combo thực đơn (FP-Growth)
- **Vì sao chọn FP-Growth thay vì Apriori?**
  - Xây dựng cây tiền tố FP-Tree nén dữ liệu, chỉ quét CSDL 2 lần, không sinh tập ứng viên bùng nổ tổ hợp như Apriori $\rightarrow$ Tốc độ nhanh hơn 10–50 lần.
- **Quy trình hoạt động**:
  1. Đọc lịch sử các đơn hàng $\ge 2$ món.
  2. Lọc các cặp món có độ hỗ trợ $\text{Support} \ge 0.05$ và độ tin cậy $\text{Confidence} \ge 0.30$.
  3. Tính chỉ số tương quan $\text{Lift} > 1.0$ (chứng minh hai món tương hỗ gọi cùng nhau).
  4. Lưu kết quả vào bảng `combo_rules` để truy vấn thời gian thực $O(1)$.
- **Cơ chế Fallback 3 lớp**:
  - Lớp 1: Luật FP-Growth $\rightarrow$ Lớp 2: Combo thủ công $\rightarrow$ Lớp 3: Top món bán chạy nhất quán.

---

## Slide 11: Thiết kế giao diện & Trải nghiệm (UI/UX Design)
- **Khách hàng (Mobile Web)**:
  - Thiết kế One-Thumb, vùng bấm $\ge 44\text{px}$, font chữ $\ge 16\text{px}$ chống auto-zoom iOS Safari.
  - Giỏ hàng nổi Optimistic UI cập nhật tức thì.
- **Bếp KDS (Tablet Landscape)**:
  - Glanceable UI, chữ to rõ, tương phản cao, Dark mode chống chói.
  - Viền ticket đổi màu theo thời gian trôi qua: Xanh ($< 5\text{p}$), Vàng ($5 - 10\text{p}$), Đỏ nhấp nháy ($> 10\text{p}$).
  - Âm thanh chuông Web Audio API tần số $880\text{ Hz}$ khi có đơn mới.

---

## Slide 12: Kịch bản Live Demo 5 phút
- **Kịch bản liền mạch trên 3 thiết bị**:
  - *Bước 1*: Quét QR bàn B12 $\rightarrow$ Xem gợi ý Combo FP-Growth $\rightarrow$ Thêm Phở bò + Nem rán + Trà đá.
  - *Bước 2*: Bấm "Gửi bếp" $\rightarrow$ Tablet bếp phát tiếng chuông, thẻ B12 xuất hiện tức thì với viền xanh.
  - *Bước 3*: Bếp bấm "Bắt đầu nấu" $\rightarrow$ Điện thoại khách đổi sang màu cam `Đang nấu` $\rightarrow$ Bếp bấm "Nấu xong".
  - *Bước 4*: Bếp bật toggle "Báo hết món" $\rightarrow$ Màn hình khách tự đổi badge "Hết món" không cần F5.
  - *Bước 5*: Khách bấm "Yêu cầu thanh toán" $\rightarrow$ Thu ngân POS nhận chuông $\rightarrow$ Áp giảm giá, thu tiền, in bill $\rightarrow$ Bàn B12 tự giải phóng về màu trắng.

---

## Slide 13: Đánh giá thực nghiệm & Đo lường hiệu năng
- **Độ trễ hệ thống (Latency Benchmark 100 requests)**:
  - $P50 = 136.94\text{ ms}$ (Mục tiêu $< 300\text{ ms}$) $\rightarrow$ **Đạt**.
  - $P95 = 184.08\text{ ms}$ (Mục tiêu $< 800\text{ ms}$) $\rightarrow$ **Đạt**.
  - Trễ WebSocket hiển thị KDS $< 300\text{ ms}$, đạt yêu cầu thời gian thực.
- **Độ chính xác thuật toán FP-Growth**:
  - Precision@3 trên tập test 20%: **$31.30\% - 36.50\%$**, vượt trội gấp ~2 lần so với Baseline gợi ý ngẫu nhiên ($16.67\%$).

---

## Slide 14: Kế hoạch phòng ngừa rủi ro & Dự phòng demo
- **Các phương án chuẩn bị**:
  - Script khởi động 1 chạm `start-all.sh` / `start-all.bat` và `docker-compose.yml`.
  - Bộ thẻ QR mẫu ép plastic dán sẵn bàn demo.
  - Video demo quay sẵn chất lượng cao $1080\text{p}$ dài 3 phút phòng trường hợp mạng trường bị chặn port WebSocket.
  - Chuẩn bị sẵn câu trả lời cho 18 câu hỏi phản biện của hội đồng.

---

## Slide 15: Kết luận & Hướng phát triển tương lai
- **Kết quả đạt được**:
  - Hoàn thành đầy đủ 100% mục tiêu đồ án: 16 bảng CSDL, 42 API endpoints, 18 màn hình Next.js cho 4 phân hệ.
  - Giải quyết trọn vẹn 5 bài toán vận hành nhà hàng thực tế.
  - Áp dụng thành công thuật toán khai phá dữ liệu FP-Growth vào thực tế ẩm thực.
- **Hướng phát triển mở rộng**:
  - Tích hợp cổng thanh toán trực tuyến tự động (MoMo, VNPay, VietQR động).
  - Tách và in phiếu trực tiếp ra máy in nhiệt LAN/Bluetooth (ESC/POS).
  - Kiểm tra định vị bàn bằng GPS / WiFi BSSID để chống khách bên ngoài quét mã rác.

---

> **LỜI CẢM ƠN**: Em xin chân thành cảm ơn Quý Thầy Cô trong Hội đồng đã lắng nghe bài thuyết trình!
