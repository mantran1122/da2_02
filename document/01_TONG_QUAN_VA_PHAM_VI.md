# 01 — Tổng quan và phạm vi

## 1. Bài toán thực tế

Một quán ăn / nhà hàng nhỏ 15–30 bàn đang làm thủ công gặp 5 vấn đề:

1. **Gọi món chậm.** Nhân viên đi lại giữa bàn và bếp, giờ cao điểm khách phải chờ 5–10 phút mới gọi được món.
2. **Sai món.** Ghi giấy rồi chuyển tay, chữ khó đọc, món về nhầm bàn.
3. **Bếp không biết thứ tự ưu tiên.** Phiếu giấy xếp chồng, bếp tự quyết định làm món nào trước, bàn đến trước có khi bị phục vụ sau.
4. **Không ai biết món đang ở đâu.** Khách hỏi “món tôi đâu rồi”, nhân viên phải chạy vào bếp hỏi lại.
5. **Hết món vẫn nhận order.** Bếp phát hiện hết nguyên liệu sau khi khách đã gọi, phải xin lỗi và đổi món.

Đề tài giải quyết cả 5 bằng cách số hoá luồng gọi món: khách tự quét QR tại bàn, order chảy thẳng xuống màn hình bếp theo thời gian thực, trạng thái món phản hồi ngược về điện thoại khách.

## 2. Mục tiêu hệ thống

| Mục tiêu | Cách đo |
| --- | --- |
| Giảm thời gian gọi món | Khách tự gọi trong < 60 giây, không cần chờ nhân viên |
| Loại bỏ sai món do chuyển tay | Order xuống bếp dạng số, không qua giấy |
| Bếp thấy order trong < 1 giây | Đo độ trễ từ lúc khách bấm “Gửi” đến lúc hiện trên màn hình bếp |
| Khách tự theo dõi được món | Trạng thái món cập nhật realtime trên điện thoại khách |
| Gợi ý món kèm có cơ sở dữ liệu | Đo precision@3 của gợi ý trên tập test |

## 3. Actor và quyền

| Actor | Vai trò | Quyền chính |
| --- | --- | --- |
| **Khách (guest)** | Người ăn tại bàn | Quét QR → xem thực đơn → gọi món → xem trạng thái món của phiên mình → gọi thêm → yêu cầu thanh toán |
| **Bếp (kitchen)** | Nhân viên bếp | Xem hàng đợi món theo thứ tự ưu tiên; đổi trạng thái món; báo hết nguyên liệu / tạm ngưng món |
| **Phục vụ (waiter)** | Nhân viên sàn | Xem sơ đồ bàn, mở/đóng phiên, chuyển bàn, gộp bàn, xác nhận món đã ra bàn |
| **Thu ngân (cashier)** | Quầy thu tiền | Xem hóa đơn, áp giảm giá, thu tiền, in hóa đơn |
| **Quản lý (admin)** | Chủ quán | Toàn quyền: thực đơn, giá, danh mục, bàn/QR, nhân viên, báo cáo, cấu hình combo |

> Với đồ án sinh viên, có thể gộp **Phục vụ + Thu ngân** thành một role `staff`, và **Quản lý** là role `admin`. Nhưng khai báo đủ 5 role trong DB để chứng minh thiết kế phân quyền.

## 4. Phạm vi MVP (bắt buộc có)

### 4.1. Quản lý thực đơn
- CRUD danh mục món (khai vị / món chính / tráng miệng / đồ uống).
- CRUD món ăn: tên, mô tả, ảnh, giá, đơn vị, thời gian chế biến ước tính, trạng thái (`available` / `out_of_stock` / `hidden`).
- CRUD **định lượng nguyên liệu** của từng món (món X cần bao nhiêu gram thịt, bao nhiêu ml nước mắm) — dùng cho nghiệp vụ báo hết món.
- CRUD **nguyên liệu** và tồn kho đơn giản.

### 4.2. Quản lý bàn và QR
- CRUD khu vực (tầng 1, sân vườn, phòng VIP) và bàn.
- Sinh mã QR cho từng bàn — QR chứa URL có **token bàn** cố định.
- Sơ đồ bàn trực quan: bàn trống / đang có khách / chờ thanh toán.

### 4.3. Phiên ăn (session) — **quyết định thiết kế then chốt**
- Khi khách quét QR lần đầu, hệ thống mở một **phiên ăn** (`dining_session`) cho bàn đó.
- Phiên có token riêng; mọi order của khách thuộc phiên này.
- Khi thanh toán xong, phiên đóng. Khách khác ngồi vào bàn sẽ mở phiên mới, **không thấy order cũ**.
- Nhờ vậy QR in trên bàn là **vĩnh viễn**, không phải in lại mỗi lần đổi khách.

### 4.4. Gọi món
- Khách xem thực đơn có ảnh, tìm kiếm, lọc theo danh mục.
- Thêm món vào **giỏ tạm** (lưu client, chưa gửi bếp).
- Ghi chú cho từng món (“không hành”, “ít cay”, “bỏ đá”).
- Bấm **Gửi bếp** → tạo order + order items trạng thái `pending` → đẩy xuống bếp ngay.
- **Gọi thêm** nhiều lần trong cùng phiên (khách ăn thêm) — mỗi lần là một `order` mới trong cùng phiên.

### 4.5. Màn hình bếp (KDS — Kitchen Display System)
- Hiển thị danh sách món chờ làm, **sắp xếp theo thời điểm gọi** (FIFO), có đếm thời gian chờ.
- Món chờ quá lâu (vượt `estimated_prep_time`) chuyển màu đỏ.
- Bếp bấm đổi trạng thái: `pending` → `cooking` → `ready` → `served`.
- Bếp báo **hết món** → món tự chuyển `out_of_stock` trên thực đơn khách.
- Nhận order mới **tức thời** qua WebSocket, không cần F5.

### 4.6. Hóa đơn và thanh toán
- Tổng hợp **tất cả order của phiên** thành 1 hóa đơn.
- Áp giảm giá (theo % hoặc số tiền, có thể theo món).
- Phụ phí: VAT, phí phục vụ (cấu hình được).
- Ghi nhận thanh toán (tiền mặt / chuyển khoản), có thể tách nhiều lần.
- In hóa đơn (PDF hoặc máy in nhiệt — với đồ án chỉ cần PDF).

### 4.7. Gợi ý món kèm (combo)
- **Cách 1 (baseline):** combo do admin cấu hình tay — mua món A gợi ý món B, C.
- **Cách 2 (nâng cao):** khai phá luật kết hợp từ lịch sử order bằng **FP-Growth** — tìm các cặp món thường được gọi chung, sinh luật `A → B` với độ hỗ trợ và độ tin cậy.
- Khi khách thêm món A vào giỏ, hệ thống gợi ý các món B có confidence cao nhất.
- Chạy offline (job định kỳ), lưu kết quả vào bảng `combo_rules` để truy vấn nhanh.

## 5. Ngoài phạm vi

- **Không** có app mobile native — web responsive, khách dùng trình duyệt.
- **Không** thanh toán online thật (VNPay/MoMo) — chỉ ghi nhận thủ công.
- **Không** quản lý nhân sự, chấm công, tính lương.
- **Không** quản lý kho nguyên liệu chuyên sâu (chỉ tồn kho đơn giản để báo hết món).
- **Không** đặt bàn trước (reservation) — chỉ phục vụ khách đến trực tiếp.
- **Không** giao hàng / mang về trong MVP.
- **Không** đa chi nhánh — 1 nhà hàng.
- **Không** in bill qua máy in nhiệt thật — chỉ xuất PDF.

## 6. Giả định

| # | Giả định | Ảnh hưởng nếu sai |
| --- | --- | --- |
| G1 | Mỗi bàn có 1 QR cố định in sẵn | Nếu phải đổi QR mỗi phiên, cần thêm bước in lại — không khả thi |
| G2 | Khách có smartphone kết nối wifi/4G của quán | Nếu không, cần menu giấy dự phòng |
| G3 | Bếp có màn hình hoặc tablet treo tường | Nếu không, phải in phiếu — mất tính realtime |
| G4 | Có ít nhất 3–6 tháng lịch sử order để FP-Growth có ý nghĩa | Nếu chưa có, dùng combo cấu hình tay và ghi rõ hạn chế |
| G5 | Một phiên ăn có thể có nhiều order (gọi thêm) | Nếu chỉ 1 order/phiên, bỏ bảng `orders`, gộp vào `sessions` |
| G6 | Nhà hàng không cần tách bill theo người trong MVP | Nếu cần, phải thêm bảng `bill_splits` — ngoài phạm vi |

## 7. Tiêu chí thành công của đồ án

- Chạy end-to-end: quét QR → gọi 3 món → bếp nhận < 1 giây → bếp đổi trạng thái → khách thấy cập nhật realtime → thanh toán → in PDF.
- Đo được **độ trễ WebSocket** (P50 và P95) từ lúc gửi đến lúc nhận.
- Có bảng so sánh **gợi ý combo**: cấu hình tay vs FP-Growth, kèm precision@3.
- Có xử lý mất kết nối: tắt wifi, mở lại, dữ liệu vẫn đúng (refetch từ DB).
- Bộ test nghiệp vụ pass 100% (đặc biệt các case: gọi thêm, chuyển bàn giữa phiên, hủy món đã nấu, tách/gộp bàn).
