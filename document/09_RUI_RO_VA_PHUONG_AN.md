# 09 — Rủi ro, phương án dự phòng và câu hỏi phản biện

## 1. Bảng rủi ro

| # | Rủi ro | Khả năng | Tác động | Phương án |
| --- | --- | --- | --- | --- |
| R1 | Reverb không kết nối được (config, firewall, port) | Trung bình | Cao | Chuyển sang polling 3 giây, giữ nguyên event class; ghi rõ là fallback |
| R2 | FP-Growth không sinh đủ luật do dữ liệu ít | Cao | Trung bình | Seeder 300+ phiên giả lập + fallback combo cấu hình tay + top bán chạy |
| R3 | Test set quá nhỏ → precision không có ý nghĩa | Trung bình | Trung bình | Dùng 80/20 chia theo thời gian; ghi rõ cỡ mẫu trong báo cáo |
| R4 | Demo cần bật 4 process, dễ quên | Cao | Cao | Viết `start-all.ps1`; checklist trước demo; video dự phòng |
| R5 | Mobile layout vỡ trên một số điện thoại | Trung bình | Trung bình | Test trên 2–3 máy thật (Android + iOS) từ tuần 6 |
| R6 | Race condition khi 2 khách gọi món cuối | Thấp | Cao | `lockForUpdate` + test T06 |
| R7 | Hội đồng hỏi “QR bị chụp gửi cho người ngoài thì sao?” | Cao | Thấp | Ghi rõ trong hạn chế + hướng mở rộng (kiểm tra IP wifi quán) |
| R8 | Hết thời gian ở phần gợi ý combo | Trung bình | Trung bình | Mốc an toàn tuần 8 đã có hệ thống chạy; combo là phần cộng thêm |

## 2. Ba kịch bản phạm vi

### Kịch bản A — Đầy đủ (mục tiêu)

WebSocket thật + FP-Growth thật + đầy đủ 18 màn hình + đo lường đầy đủ.

### Kịch bản B — Cắt WebSocket (dự phòng 1)

- Giữ nguyên kiến trúc event/broadcast, nhưng transport là **polling 3 giây**.
- Vẫn có `OrderCreated` event class, chỉ không đẩy qua Reverb mà client tự fetch.
- Báo cáo ghi rõ: “đã thiết kế broadcast, triển khai fallback polling do hạn chế môi trường”.
- Vẫn giữ FP-Growth và đầy đủ nghiệp vụ.

### Kịch bản C — Cắt gợi ý combo thông minh (dự phòng 2)

- Chỉ dùng combo do admin cấu hình tay.
- Giữ bảng `combo_rules` với `source = 'manual'`.
- Báo cáo trình bày: “đã thiết kế pipeline FP-Growth (gồm script, job, bảng luật), phạm vi triển khai giới hạn ở combo thủ công do dữ liệu thực tế chưa đủ”.

> Quyết định chuyển kịch bản vào **cuối tuần 9** (cho WebSocket) và **cuối tuần 10** (cho combo).

## 3. Câu hỏi hội đồng hay hỏi

### 3.1. Về thiết kế phiên ăn

**Q: Sao phải có bảng `dining_sessions`, sao không gắn order trực tiếp vào bàn?**

> A: Vì bàn là thực thể **vật lý cố định**, còn khách là thực thể **thay đổi theo thời gian**. Nếu gắn order vào bàn, khách A ăn xong rời đi, khách B ngồi vào sẽ thấy order của A. Bảng phiên tách hai khái niệm này: bàn tồn tại mãi, phiên mở/đóng theo từng lượt khách. Nhờ vậy QR in trên bàn dùng vĩnh viễn, không phải in lại mỗi lần đổi khách.

**Q: Nếu khách quên đóng phiên rồi bỏ đi thì sao?**
> A: Phiên vẫn mở, bàn hiện “đang có khách”. Nhân viên phục vụ có quyền đóng phiên thủ công (nút trong sơ đồ bàn). Ngoài ra có thể thêm job tự đóng phiên quá 8 tiếng — em ghi trong hướng mở rộng.

**Q: 2 nhóm khách ngồi chung bàn thì sao?**
> A: Trong MVP, mỗi bàn một phiên — nhóm thứ hai dùng chung phiên, bill gộp. Đây là hạn chế có ghi rõ. Hướng mở rộng là cho phép nhiều phiên/bàn với cột `sub_session_no`, hoặc dùng chức năng tách bill.

### 3.2. Về WebSocket

**Q: Vì sao dùng Reverb mà không dùng Pusher?**
> A: Ba lý do: (1) Pusher có giới hạn gói free và phụ thuộc mạng — không demo offline được; (2) Reverb là package first-party của Laravel 11+, nói giao thức Pusher nên vẫn dùng `laravel-echo` chuẩn; (3) chạy local cho phép em kiểm soát hoàn toàn khi debug và đo latency chính xác.

**Q: Nếu mất kết nối WebSocket thì sao?**
> A: WebSocket chỉ là kênh thông báo, **DB luôn là nguồn sự thật**. Ghi DB xong mới broadcast. Khi client reconnect, em refetch toàn bộ — dữ liệu không bao giờ sai. Em có test W03 cho case tắt wifi 10 giây.

**Q: Làm sao biết sự kiện có đến đúng người?**
> A: Mỗi sự kiện khai báo `broadcastOn()` chỉ tới channel cụ thể: kitchen theo nhà hàng, session theo token phiên. `routes/channels.php` kiểm tra quyền khi subscribe — người lạ nhận 403.

**Q: Độ trễ bao nhiêu?**
> A: [Điền số thật.] Em đo 100 lần, P50 = __ ms, P95 = __ ms trên LAN. So với polling 2 giây thì trễ trung bình giảm từ ~1000 ms xuống __ ms, và số request mỗi thiết bị giảm từ 30/phút xuống 0.

**Q: Khi có 5 tablet bếp, sự kiện có bị nhân bản không?**
> A: Không. Broadcast là fan-out — mỗi subscriber nhận 1 bản. Em có test W07 mở 3 tab bếp cùng lúc, cả 3 nhận đúng 1 lần.

### 3.3. Về gợi ý combo

**Q: Em dùng thuật toán gì? Vì sao?**
> A: FP-Growth. Em chọn vì nó nhanh hơn Apriori (không sinh ứng viên, chỉ 2 lượt quét DB, nén vào FP-Tree), và đây là thuật toán chuẩn cho market basket analysis trong tài liệu. Với dữ liệu nhỏ cả hai đều chạy được nhưng FP-Growth là lựa chọn đúng về mặt kỹ thuật.

**Q: Support, confidence, lift là gì? Khác nhau thế nào?**
> A: Support = tỉ lệ đơn có cả A và B. Confidence = P(B|A) — gọi A thì bao nhiêu phần trăm gọi B. Lift = confidence / support(B) — đo tương quan thật. Ví dụ trà đá ai cũng gọi nên confidence của A→trà đá cao, nhưng lift ≈ 1 nghĩa là gọi A không làm tăng xác suất gọi trà đá. Em lọc theo lift ≥ 1.2 để tránh loại gợi ý vô nghĩa này.

**Q: Sao không dùng collaborative filtering / deep learning?**
> A: Với quán nhỏ vài trăm phiên, collaborative filtering thiếu dữ liệu (cold start trầm trọng) và không giải thích được. FP-Growth cho ra luật đọc hiểu được (“72% khách gọi phở cũng gọi trà đá”), phù hợp để hiển thị cho khách và để admin kiểm chứng. Đây là lựa chọn phù hợp quy mô bài toán.

**Q: Precision@3 của em bao nhiêu? So với baseline thế nào?**
> A: [Điền số thật.] Em so với 3 baseline: random __%, top-bán-chạy __%, combo tay __%, FP-Growth __%. [Nếu thua top-bán-chạy thì nói thẳng: “với quán nhỏ, top-bán-chạy là baseline rất mạnh; FP-Growth vượt random rõ rệt nhưng chưa vượt top-bán-chạy — em đề xuất hướng kết hợp fallback”.]

**Q: Em chia train/test thế nào?**
> A: Chia theo **thời gian** — 80% phiên cũ nhất làm train, 20% phiên mới nhất làm test. Không chia ngẫu nhiên, vì chia ngẫu nhiên sẽ để thông tin tương lai rò rỉ vào train, làm kết quả đẹp giả tạo.

### 3.4. Về nghiệp vụ

**Q: Trạng thái món có mấy bước? Vì sao không gộp?**
> A: 5 trạng thái: pending → cooking → ready → served, và cancelled. Em tách `ready` khỏi `served` vì hai thời điểm khác nhau: bếp nấu xong (ready) và nhân viên bưng ra bàn (served). Tách ra giúp đo được thời gian chờ bưng và phát hiện món nằm ở quầy quá lâu.

**Q: Hủy món thì nguyên liệu tính sao?**
> A: Phụ thuộc trạng thái. Món `pending` chưa động vào nguyên liệu → hoàn kho. Món `cooking` đã bắt đầu nấu → không hoàn. Món `ready`/`served` → không cho hủy. Đây là chi tiết em xử lý trong `ItemStateMachine` + `StockService`.

**Q: Giá món đổi giữa chừng thì bill tính sao?**
> A: `order_items.unit_price` là snapshot giá lúc gọi. Bill cũ không đổi khi thực đơn đổi giá. Đây là lý do em không join sang `dishes.price` khi tính bill.

**Q: VAT tính trên cái gì? Trước hay sau phí phục vụ?**
> A: VAT tính trên (subtotal − discount + service_charge). Tức là phí phục vụ chịu VAT. Đây là cách nhiều nhà hàng làm; em có test riêng `test_vat_applies_after_service_charge` để bắt lỗi nếu ai đó đảo thứ tự.

**Q: Làm sao chống 2 khách cùng gọi món cuối cùng?**
> A: `Dish::lockForUpdate()` trong transaction khi kiểm tra tồn kho. Khách thứ hai sẽ chờ lock, thấy kho đã âm, nhận lỗi “chỉ còn 0 phần”. Em có test T06 mô phỏng case này.

### 3.5. Câu hỏi khó

**Q: Đề tài này có gì khác app gọi món có sẵn (iPOS, PosApp, KiotViet)?**
> A: Em không claim mới về ý tưởng. Đóng góp của em là: (1) thiết kế CSDL chuẩn hoá với khái niệm **phiên ăn** tách khỏi bàn, giải quyết bài toán QR vĩnh viễn; (2) triển khai WebSocket first-party (Reverb) với đo lường latency thực; (3) pipeline FP-Growth có đánh giá so baseline trung thực. Đây là đồ án học phần, mục tiêu là thể hiện năng lực kỹ thuật.

**Q: QR bị chụp gửi cho người ngoài, họ gọi món phá thì sao?**
> A: Đây là hạn chế em ghi rõ. Trong MVP, token QR là UUID khó đoán nhưng không chặn được người có ảnh. Hướng mở rộng: (a) kiểm tra IP thuộc dải wifi quán, (b) yêu cầu nhân viên xác nhận order đầu tiên của phiên, (c) QR động đổi mỗi 5 phút (nhưng phải có màn hình hiển thị tại bàn).

**Q: Em có test với quán thật chưa?**
> A: [Trả lời thật.] Nếu có: em quan sát 1 quán X, tham khảo mẫu phiếu order và bill thật. Nếu chưa: em dựa trên mẫu phiếu giấy phổ biến và dữ liệu seed mô phỏng, ghi rõ đây là hạn chế.

**Q: Nếu quán có 200 bàn thì kiến trúc này chịu được không?**
> A: Về DB, có index đầy đủ và truy vấn KDS chỉ lọc `status IN ('pending','cooking')` — ổn. Về WebSocket, Reverb có thể scale ngang với Redis làm backend pub/sub. Nút thắt thực tế sẽ là job FP-Growth (đọc toàn bộ lịch sử) — giải pháp là chạy incremental hoặc chuyển sang Spark nếu dữ liệu lớn.

**Q: Sao không làm tách bill theo người?**
> A: Đó là tính năng phức tạp (cần thêm bảng `bill_splits`, luồng chia tiền, xử lý làm tròn) và không cần cho MVP. Em ghi trong hạn chế và hướng mở rộng. Với đồ án 12 tuần, ưu tiên đúng luồng chính quan trọng hơn.

## 4. Những gì ghi vào phần “Hạn chế”

- Chưa hỗ trợ nhiều phiên trên cùng một bàn (2 nhóm khách chung bàn).
- QR tĩnh có thể bị chụp và dùng từ xa.
- Chưa có thanh toán online thật.
- Chưa tách bill theo người.
- Gợi ý combo cần tối thiểu ~200 phiên để có ý nghĩa thống kê.
- Chưa có app mobile native (chỉ web responsive).
- Chưa quản lý kho nguyên liệu chuyên sâu (chỉ tồn kho đơn giản).

## 5. Hướng mở rộng

| Hướng | Giá trị |
| --- | --- |
| Thanh toán online (VNPay/MoMo/QR ngân hàng) | Giảm thao tác thu tiền |
| Tách bill theo người | Phù hợp nhóm bạn ăn chung |
| QR động (đổi mỗi 5 phút) | Chống dùng QR từ xa |
| Đặt bàn trước (reservation) | Mở rộng mô hình |
| Dự báo nhu cầu nguyên liệu | Giảm lãng phí |
| Gợi ý cá nhân hoá theo lịch sử khách | Cần tài khoản khách hàng |
| Đa chi nhánh | Mô hình chuỗi |
| In bill qua máy in nhiệt | Dùng thực tế |
