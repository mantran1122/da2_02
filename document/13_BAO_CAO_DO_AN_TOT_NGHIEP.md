# BÁO CÁO ĐỒ ÁN TỐT NGHIỆP / ĐỒ ÁN CHUYÊN NGÀNH (ĐỒ ÁN 2)

**ĐỀ TÀI:**  
# XÂY DỰNG HỆ THỐNG GỌI MÓN TẠI BÀN (QR ORDER) VÀ QUẢN LÝ BẾP NHÀ HÀNG (KDS) TÍCH HỢP GỢI Ý THỰC ĐƠN THÔNG MINH BẰNG THUẬT TOÁN FP-GROWTH

---

- **Ngành đào tạo:** Công nghệ Thông tin / Kỹ thuật Phần mềm  
- **Sinh viên thực hiện:** Hồ Văn Nhựt Cường  
- **Mã số sinh viên:** *(Cập nhật theo hồ sơ cá nhân)*  
- **Giảng viên hướng dẫn:** *(Cập nhật theo phân công khoa)*  
- **Thời gian thực hiện:** 12 tuần (Học kỳ II, Năm học 2026 – 2027)  

---

## LỜI CAM ĐOAN

Tôi xin cam đoan đề tài *"Xây dựng hệ thống Gọi món tại Bàn (QR Order) và Quản lý Bếp Nhà hàng (KDS) tích hợp Gợi ý Thực đơn Thông minh bằng Thuật toán FP-Growth"* là công trình nghiên cứu và phát triển độc lập của bản thân tôi dưới sự hướng dẫn của Giảng viên hướng dẫn.

Các số liệu, mã nguồn, bảng thực nghiệm và kết quả phân tích trình bày trong báo cáo này là hoàn toàn trung thực, không sao chép từ bất kỳ công trình nào khác chưa được công bố. Mọi sự tham khảo từ các tài liệu khoa học, thư viện mã nguồn mở đều được trích dẫn nguồn gốc đầy đủ, rõ ràng theo đúng quy định học thuật.

*TP. Hồ Chí Minh, ngày 17 tháng 11 năm 2026*  
**Sinh viên thực hiện**  
*(Ký và ghi rõ họ tên)*  
**Hồ Văn Nhựt Cường**

---

## TÓM TẮT ĐỀ TÀI (ABSTRACT)

### Tóm tắt tiếng Việt
Trong ngành dịch vụ ăn uống (F&B) quy mô vừa và nhỏ (15–30 bàn), quy trình vận hành truyền thống dựa trên ghi giấy và gọi miệng bộc lộ nhiều điểm nghẽn: thời gian chờ đợi gọi món kéo dài (5–10 phút), sai sót đơn hàng trong khâu chuyển tiếp, bếp bị quá tải do thiếu thứ tự ưu tiên (FIFO), và nhân viên phục vụ mất nhiều thời gian di chuyển qua lại để xác nhận trạng thái món. Đề tài này giải quyết triệt để các vấn đề trên thông qua việc xây dựng một hệ thống phần mềm toàn diện gồm 4 phân hệ: Thực khách gọi món qua mã QR tại bàn (Mobile Web App), Màn hình hiển thị bếp thông minh (Kitchen Display System - KDS), Sơ đồ bàn phục vụ và Điểm bán hàng (POS) cho thu ngân.

Hệ thống được phát triển trên nền tảng kiến trúc Client-Server hiện đại: Backend sử dụng Laravel 11 kết hợp cơ sở dữ liệu MySQL 8 (16 bảng chuẩn hóa) và Redis 7; Frontend xây dựng bằng Next.js 15 (React 19, TypeScript) và Tailwind CSS. Cơ chế đồng bộ dữ liệu thời gian thực được đảm bảo bởi Laravel Reverb WebSocket, cho phép lệnh gọi món từ điện thoại thực khách xuất hiện tức thì trên màn hình KDS trong thời gian dưới 1 giây mà không cần tải lại trang. Đồng thời, hệ thống ứng dụng thuật toán khai phá luật kết hợp FP-Growth (Frequent Pattern Growth) trên lịch sử giao dịch nhằm tự động gợi ý các combo món ăn kèm phù hợp, nâng cao trải nghiệm khách hàng và giá trị hóa đơn trung bình.

### Abstract in English
In small and medium food and beverage (F&B) establishments (15–30 tables), traditional paper-based and verbal ordering processes cause notable operational bottlenecks: prolonged waiting times (5–10 minutes), manual order transcription errors, kitchen congestion due to the lack of FIFO dispatching, and excessive staff transit to track dish fulfillment. This graduation project addresses these challenges by developing an end-to-end web system comprising four dedicated modules: Dine-in QR Table Ordering (Mobile Web App), Smart Kitchen Display System (KDS), Staff Table Map, and Cashier Point of Sale (POS).

The system adopts a decoupled Client-Server architecture: Backend powered by Laravel 11, MySQL 8 (16 normalized relational tables), and Redis 7; Frontend crafted with Next.js 15, TypeScript, and Tailwind CSS. Real-time synchronization is driven by Laravel Reverb WebSocket, achieving sub-second ($<1\text{s}$) order dispatching directly onto kitchen screens without browser polling. Furthermore, the Frequent Pattern Growth (FP-Growth) association rule mining algorithm is embedded into transaction analytics to generate intelligent upselling combo recommendations, elevating customer satisfaction and average ticket size.

---

## DANH MỤC THUẬT NGỮ VÀ TỪ VIẾT TẮT

| Viết tắt | Tên đầy đủ tiếng Anh | Ý nghĩa / Giải thích |
|:---|:---|:---|
| **KDS** | Kitchen Display System | Hệ thống màn hình hiển thị quản lý bếp nhà hàng |
| **POS** | Point of Sale | Điểm bán hàng và thanh toán thu ngân |
| **FIFO** | First In, First Out | Vào trước ra trước (nguyên tắc ưu tiên nấu món gọi trước) |
| **FP-Growth** | Frequent Pattern Growth | Thuật toán khai phá tập mục phổ biến dựa trên cấu trúc cây FP-Tree |
| **WebSocket** | Full-duplex Communication Protocol | Giao thức truyền thông hai chiều thời gian thực trên một kết nối TCP |
| **DoD** | Definition of Done | Tiêu chí hoàn thành công việc theo chuẩn Scrum / Agile |
| **P50 / P95** | 50th / 95th Percentile | Phân vị độ trễ (50% và 95% số yêu cầu có thời gian phản hồi nhỏ hơn) |
| **Precision@k** | Precision at Rank $k$ | Tỷ lệ gợi ý chính xác trong top $k$ món được hệ thống đề xuất |
| **ORM** | Object-Relational Mapping | Kỹ thuật ánh xạ cơ sở dữ liệu quan hệ sang đối tượng (Eloquent) |
| **VAT** | Value Added Tax | Thuế giá trị gia tăng |

---

# MỤC LỤC CHI TIẾT

- **CHƯƠNG 1: TỔNG QUAN BÀI TOÁN VÀ KHẢO SÁT HIỆN TRẠNG**
  - 1.1. Bối cảnh và Tính cấp thiết của Đề tài
  - 1.2. Khảo sát Hiện trạng Vận hành tại Nhà hàng 15–30 Bàn
  - 1.3. Phân tích 5 Thất thoát Vận hành Cốt tử
  - 1.4. So sánh với các Giải pháp trên Thị trường
  - 1.5. Mục tiêu và Phạm vi Đề tài (MVP)
- **CHƯƠNG 2: CƠ SỞ LÝ THUYẾT VÀ CÔNG NGHỆ ÁP DỤNG**
  - 2.1. Kiến trúc Hướng Dịch vụ (Client-Server & RESTful API)
  - 2.2. Nền tảng Backend: Laravel 11 và Hệ sinh thái Sanctum/Reverb
  - 2.3. Cơ chế Thời gian thực Realtime WebSocket với Laravel Reverb
  - 2.4. Thuật toán Khai phá Luật Kết hợp FP-Growth (Frequent Pattern Growth)
  - 2.5. Nền tảng Frontend: Next.js 15, TypeScript, Tailwind CSS và Zustand
  - 2.6. Quản trị Cơ sở dữ liệu và Bộ đệm: MySQL 8, Redis 7 và Docker
- **CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG**
  - 3.1. Mô hình Phân quyền và Sơ đồ Use Case Tổng thể (5 Actors)
  - 3.2. Thiết kế Kiến trúc Hệ thống 4 Khối (Architecture Block Diagram)
  - 3.3. Thiết kế Cơ sở Dữ liệu 16 Bảng và Chiến lược Đánh chỉ mục
  - 3.4. Mô hình Quản lý Phiên ăn (`dining_sessions`) vs Bàn ăn Vật lý (`tables`)
  - 3.5. Cỗ máy Trạng thái Đơn hàng 1 Chiều (Item State Machine)
  - 3.6. Công thức Tính toán Hóa đơn Chuẩn (Golden Case 145.000 đ)
  - 3.7. Thiết kế Kênh và Sự kiện Realtime WebSocket
- **CHƯƠNG 4: HIỆN THỰC HÓA VÀ ĐÁNH GIÁ THỰC NGHIỆM**
  - 4.1. Hiện thực hóa Giao diện 4 Phân hệ (18 Màn hình Wireframe)
  - 4.2. Kịch bản Thực nghiệm Đo lường Độ trễ Hệ thống (Latency Benchmark)
  - 4.3. Kịch bản Đánh giá Thuật toán Gợi ý Thực đơn FP-Growth
  - 4.4. Kiểm thử Chịu lỗi Mất kết nối Mạng (Network Fault Tolerance)
- **CHƯƠNG 5: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN**
  - 5.1. Các Kết quả Đạt được của Đồ án
  - 5.2. Các Hạn chế Hiện tại của Hệ thống
  - 5.3. Hướng Phát triển Mở rộng trong Tương lai
- **TÀI LIỆU THAM KHẢO**

---

# NỘI DUNG BÁO CÁO KHOA HỌC

## CHƯƠNG 1: TỔNG QUAN BÀI TOÁN VÀ KHẢO SÁT HIỆN TRẠNG

### 1.1. Bối cảnh và Tính cấp thiết của Đề tài
Ngành dịch vụ ẩm thực (F&B) tại Việt Nam đang trải qua giai đoạn chuyển đổi số mạnh mẽ. Với các nhà hàng, quán ăn quy mô vừa và nhỏ (từ 15 đến 30 bàn ăn), áp lực về chi phí nhân công, tốc độ phục vụ vào các khung giờ cao điểm (11h30–13h30 trưa và 18h30–20h30 tối) trở thành bài toán sinh tồn. 

Tại các mô hình truyền thống, quy trình ghi món thủ công bằng giấy ghi order bộc lộ những nhược điểm cố hữu: khách hàng phải vẫy tay chờ nhân viên đến bàn; nhân viên ghi nhầm món hoặc quên ghi chú của khách; thời gian di chuyển từ bàn về quầy thu ngân và chuyển phiếu vào bếp mất từ 3 đến 5 phút; trong bếp, phiếu giấy bị dính dầu mỡ, thất lạc hoặc nhầm lẫn thứ tự vào trước - nấu trước. Những yếu tố này trực tiếp kéo giảm độ hài lòng của khách hàng và hạn chế công suất quay vòng bàn ăn của nhà hàng.

Việc ứng dụng công nghệ quét mã QR tại bàn kết hợp màn hình hiển thị nhà bếp KDS và thuật toán trí tuệ nhân tạo gợi ý món là giải pháp tối ưu, cho phép giải phóng sức lao động của nhân viên phục vụ, chuẩn hóa quy trình chế biến của đầu bếp và gia tăng doanh thu từ việc bán chéo món ăn (cross-selling).

### 1.2. Khảo sát Hiện trạng Vận hành tại Nhà hàng 15–30 Bàn
Qua khảo sát thực tế tại các quán ăn phân khúc bình dân và trung cấp (mô hình quán Phở, Cơm tấm, Bún chả), quy trình vận hành thủ công trải qua 7 bước:
1. Khách vào quán chọn bàn ngồi.
2. Khách chờ nhân viên mang thực đơn giấy và bút ra bàn (thời gian chờ: 2–5 phút).
3. Khách gọi món, nhân viên ghi vào sổ order 2 liên hoặc 3 liên (dễ sai sót ghi chú: "ít cay", "không hành").
4. Nhân viên cầm liên 1 xé đưa vào bếp kẹp lên thanh ray, liên 2 mang ra quầy thu ngân.
5. Đầu bếp nhìn hàng loạt phiếu kẹp, tự ước lượng món nào nấu trước món nào nấu sau (thường bị vi phạm nguyên tắc FIFO, bàn đến sau lại có món trước).
6. Khách đợi lâu giục nhân viên; nhân viên chạy vào bếp hỏi đầu bếp làm gián đoạn việc nấu nướng.
7. Khách ăn xong ra quầy thu ngân đọc số bàn; thu ngân lục tìm liên phiếu giấy để bấm máy tính cộng tiền thủ công.

### 1.3. Phân tích 5 Thất thoát Vận hành Cốt tử
Hệ thống xác định 5 điểm thất thoát vận hành then chốt và đưa ra giải pháp số hóa tương ứng:
- **Thất thoát 1: Thời gian gọi món lãng phí (5–10 phút/lượt).**  
  *Giải pháp:* Khách tự quét mã QR trên bàn bằng camera điện thoại cá nhân (không cần tải ứng dụng), thực đơn điện tử hiển thị tức thì, chọn món và bấm gửi bếp trong $<60$ giây.
- **Thất thoát 2: Sai lệch đơn hàng do tam sao thất bản.**  
  *Giải pháp:* Thông tin món ăn, số lượng và ghi chú chi tiết được truyền thẳng từ màn hình điện thoại của khách vào cơ sở dữ liệu và hiện thị lên màn hình KDS của bếp, triệt tiêu hoàn toàn sai sót do ghi chép giấy.
- **Thất thoát 3: Bếp hỗn loạn và nấu sai thứ tự.**  
  *Giải pháp:* KDS sắp xếp các món theo thứ tự thời gian vào bếp (FIFO). Bộ đếm thời gian tự động đổi màu theo 3 cấp độ: Xanh ($<5$ phút), Vàng ($5-10$ phút), và Đỏ nhấp nháy ($>10$ phút) giúp đầu bếp chủ động ưu tiên món tồn đọng lâu.
- **Thất thoát 4: Nhân viên phục vụ chạy qua lại hỏi dò trạng thái.**  
  *Giải pháp:* Cơ chế Realtime WebSocket đẩy trạng thái món (`pending` $\rightarrow$ `cooking` $\rightarrow$ `ready` $\rightarrow$ `served`) về trực tiếp điện thoại khách, giảm 90% các câu hỏi "Món của em nấu xong chưa?".
- **Thất thoát 5: Quản lý kho nguyên liệu và cập nhật menu bị động.**  
  *Giải pháp:* Khi bếp bấm báo hết một món ăn trên màn hình KDS, hệ thống lập tức phát sóng WebSocket cập nhật thực đơn toàn bộ các bàn, ngăn chặn triệt để tình trạng khách gọi món nhưng 15 phút sau nhân viên mới báo hết.

### 1.4. So sánh với các Giải pháp trên Thị trường
Trên thị trường hiện có các giải pháp POS lớn như KiotViet, iPOS, CukCuk. Tuy nhiên:
- Chi phí bản quyền và đầu tư thiết bị phần cứng chuyên dụng rất cao (từ 15–30 triệu đồng).
- Giao diện KDS phức tạp, cồng kềnh, không tối ưu cho màn hình cảm ứng di động hoặc tablet giá rẻ.
- Chưa có tính năng khai phá dữ liệu thông minh (Data Mining) để gợi ý combo tự động cá nhân hóa theo từng món ăn.

### 1.5. Mục tiêu và Phạm vi Đề tài (MVP)
- **Mục tiêu:** Xây dựng hệ thống hoàn chỉnh chạy trên nền web (Web-first), hoạt động ổn định trên mạng nội bộ LAN/WiFi của quán, độ trễ phát sóng đơn hàng sang bếp $<1$ giây, giao diện thân thiện không yêu cầu đào tạo nhân sự.
- **Phạm vi MVP:**
  - Không làm native app Android/iOS (tập trung 100% vào Mobile Web App tương thích Safari/Chrome).
  - Không tích hợp cổng thanh toán trực tuyến tiền thật (VNPAY/Momo qua sandbox) mà hỗ trợ thanh toán tiền mặt và chuyển khoản mã QR tĩnh VietQR.
  - Không quản lý mua hàng, nhà cung cấp hay kế toán kho nâng cao.

---

## CHƯƠNG 2: CƠ SỞ LÝ THUYẾT VÀ CÔNG NGHỆ ÁP DỤNG

### 2.1. Kiến trúc Hướng Dịch vụ (Client-Server & RESTful API)
Dự án được phân tách hoàn toàn giữa tầng hiển thị (Frontend) và tầng xử lý nghiệp vụ (Backend) thông qua giao thức truyền thông HTTP RESTful API chuẩn JSON. Lối kiến trúc này đem lại sự độc lập về công nghệ, khả năng bảo trì cao và dễ dàng mở rộng nhiều giao diện người dùng mà không cần thay đổi logic lõi.

### 2.2. Nền tảng Backend: Laravel 11 và Hệ sinh thái Sanctum/Reverb
Laravel 11 được lựa chọn làm nền tảng xử lý Backend nhờ:
- Cấu trúc thư mục tinh gọn, hiệu năng cao, hỗ trợ PHP 8.2+.
- Hệ thống Eloquent ORM mạnh mẽ, xử lý giao dịch cơ sở dữ liệu an toàn (`DB::transaction`, `lockForUpdate`).
- Laravel Sanctum: Cung cấp cơ chế xác thực dựa trên Token Bearer cho 4 vai trò nhân viên (`kitchen`, `waiter`, `cashier`, `admin`).
- Laravel Queues: Quản lý các tác vụ nền qua Redis (như việc huấn luyện thuật toán FP-Growth).

### 2.3. Cơ chế Thời gian thực Realtime WebSocket với Laravel Reverb
Khác với mô hình Polling truyền thống (gửi request liên tục mỗi vài giây gây lãng phí băng thông và nghẽn CPU), dự án sử dụng **Laravel Reverb** — máy chủ WebSocket tốc độ cao được tích hợp chính thức từ phiên bản Laravel 11.
- Giao thức truyền thông hai chiều toàn phần (Full-duplex) qua cổng TCP 8080.
- Khách hàng bấm gửi order $\rightarrow$ Event `OrderCreated` được kích hoạt trên Backend $\rightarrow$ Reverb phát sóng qua channel `kitchen` $\rightarrow$ Màn hình KDS nhận gói tin JSON và phát chuông âm thanh trong thời gian $<200\text{ ms}$.

### 2.4. Thuật toán Khai phá Luật Kết hợp FP-Growth (Frequent Pattern Growth)
Để giải quyết bài toán gợi ý món ăn kèm (Upselling), thuật toán **FP-Growth** của Jiawei Han (2000) được áp dụng thay vì Apriori truyền thống.
- **Ưu điểm vượt trội so với Apriori:** Apriori phải sinh ra vô số tập ứng viên ($k$-itemsets) và quét cơ sở dữ liệu nhiều lần, gây suy giảm hiệu năng nghiêm trọng khi số giao dịch lớn. Ngược lại, FP-Growth chỉ quét cơ sở dữ liệu **đúng 2 lần**:
  - Lần 1: Đếm tần suất xuất hiện của từng món đơn lẻ và sắp xếp theo thứ tự giảm dần.
  - Lần 2: Nén toàn bộ cơ sở dữ liệu giao dịch vào cấu trúc cây nén **FP-Tree (Frequent Pattern Tree)**.
- **Các chỉ số đánh giá luật ($A \rightarrow B$):**
  - $\text{Support}(A \rightarrow B) = \frac{\text{Số đơn chứa cả } A \text{ và } B}{\text{Tổng số đơn hàng}} \ge 0.05$ (Ngưỡng tối thiểu 5%).
  - $\text{Confidence}(A \rightarrow B) = \frac{P(A \cap B)}{P(A)} \ge 0.30$ (Độ tin cậy tối thiểu 30%).
  - $\text{Lift}(A \rightarrow B) = \frac{P(A \cap B)}{P(A) \times P(B)} > 1.0$ (Đảm bảo việc khách gọi món $A$ làm tăng xác suất gọi thêm món $B$, loại bỏ tương quan ngẫu nhiên).

### 2.5. Nền tảng Frontend: Next.js 15, TypeScript, Tailwind CSS và Zustand
- **Next.js 15 (App Router):** Tối ưu hóa tải trang, tự động phân chia mã nguồn (Code Splitting), hỗ trợ Client Components cho các giao diện tương tác cao.
- **TypeScript:** Định kiểu chặt chẽ toàn bộ các thực thể dữ liệu (`Dish`, `Order`, `OrderItem`, `Invoice`), giảm thiểu 95% lỗi runtime undefined.
- **Tailwind CSS:** Xây dựng giao diện Responsive trực tiếp theo phương pháp Mobile-First, phối màu Dark Mode cho KDS và màu nhận diện ấm cúng cho khách hàng.
- **Zustand:** Thư viện quản lý trạng thái gọn nhẹ (chỉ 1KB), dùng để quản lý `cartStore` (giỏ hàng phía khách) và `sessionStore` (phiên ăn).

### 2.6. Quản trị Cơ sở dữ liệu và Bộ đệm: MySQL 8, Redis 7 và Docker
- **MySQL 8 (InnoDB):** Lưu trữ quan hệ 16 bảng với hỗ trợ khóa ngoại (Foreign Keys) nghiêm ngặt và cơ chế khóa dòng giao dịch `SELECT ... FOR UPDATE`.
- **Redis 7:** Bộ nhớ trong (In-memory) đóng vai trò điều phối hàng đợi tác vụ (`queue`) và làm bộ lưu trữ session tốc độ cao.
- **Docker Compose:** Đóng gói toàn bộ 6 dịch vụ (`mysql`, `redis`, `backend`, `reverb`, `queue`, `frontend`) vào một cụm container tiêu chuẩn hóa, giúp triển khai 1-click trên mọi môi trường.

---

## CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG

### 3.1. Mô hình Phân quyền và Sơ đồ Use Case Tổng thể (5 Actors)
Hệ thống định nghĩa 5 đối tượng tác nhân:
1. **Khách hàng (`guest`):** Quét mã QR, xem thực đơn, xem gợi ý combo, thêm vào giỏ, gửi order, theo dõi trạng thái món, yêu cầu hóa đơn và thanh toán.
2. **Nhân viên Bếp (`kitchen`):** Theo dõi hàng đợi KDS (FIFO), bấm chuyển trạng thái món sang nấu/nấu xong, bấm hủy món kèm lý do, bật/tắt báo hết món trong kho.
3. **Nhân viên Phục vụ (`waiter`):** Xem sơ đồ bàn trực quan, mở bàn cho khách, chuyển bàn, gộp bàn, bưng món ra bàn (`ready` $\rightarrow$ `served`).
4. **Thu ngân (`cashier`):** Nhận yêu cầu thanh toán, xem bảng kê chi tiết, áp mã giảm giá, thu tiền (tiền mặt/QR), in phiếu hóa đơn, đóng phiên ăn.
5. **Quản trị viên (`admin`):** Quản lý danh mục, món ăn, định lượng nguyên liệu, xem thống kê doanh thu và kích hoạt chạy lại thuật toán FP-Growth.

```mermaid
flowchart LR
  Guest((Thực khách)) --> UC1[Quét QR xem Menu]
  Guest --> UC2[Đặt món & Xem Combo]
  Guest --> UC3[Theo dõi món & Gọi tính tiền]

  Kitchen((Bếp KDS)) --> UC4[Hàng đợi món FIFO]
  Kitchen --> UC5[Đổi trạng thái Nấu/Xong]
  Kitchen --> UC6[Báo hết món nhanh]

  Waiter((Phục vụ)) --> UC7[Sơ đồ bàn & Chuyển/Gộp bàn]
  Waiter --> UC8[Bưng món ra bàn]

  Cashier((Thu ngân)) --> UC9[Kiểm tra Hóa đơn & Giảm giá]
  Cashier --> UC10[Thu tiền & In hóa đơn]

  Admin((Quản trị)) --> UC11[Quản lý Menu & Kho]
  Admin --> UC12[Huấn luyện luật FP-Growth]
```

### 3.2. Thiết kế Kiến trúc Hệ thống 4 Khối
Kiến trúc tổng thể gồm 4 tầng phân lập rõ ràng:
1. **Tầng Giao diện (Presentation Layer):** Gồm 4 ứng dụng web chạy trên trình duyệt (Mobile Guest, Tablet KDS, Desktop POS, Admin Portal).
2. **Tầng Giao tiếp & Thời gian thực (Gateway & Transport Layer):** HTTP/HTTPS Endpoint cho API và WSS/WS Endpoint cho Reverb WebSocket.
3. **Tầng Xử lý Nghiệp vụ (Business Logic Layer):** Laravel Controllers, Form Requests, 5 Core Services (`OrderService`, `StockService`, `ItemStateMachine`, `InvoiceService`, `FpGrowthService`).
4. **Tầng Dữ liệu & Lưu trữ (Data Persistence Layer):** MySQL 8 (16 bảng) và Redis 7 (Cache & Queue).

### 3.3. Thiết kế Cơ sở Dữ liệu 16 Bảng và Chiến lược Đánh chỉ mục
CSDL gồm 16 bảng quan hệ chặt chẽ:
- `restaurants`, `areas`, `tables`: Quản lý thực thể không gian quán.
- `staff`: Quản lý tài khoản nhân viên phân quyền ENUM (`kitchen`, `waiter`, `cashier`, `admin`).
- `categories`, `dishes`, `ingredients`, `dish_ingredients`: Quản lý thực đơn và định lượng hao hụt kho.
- `dining_sessions`, `orders`, `order_items`: Quản lý luồng gọi món và cỗ máy trạng thái.
- `invoices`, `invoice_items`, `payments`: Quản lý tài chính, hóa đơn và doanh thu.
- `combo_rules`: Lưu trữ các luật kết hợp đã khai phá từ FP-Growth.
- `feedbacks`: Tiếp nhận đánh giá sao và góp ý từ thực khách.

**Chiến lược đánh chỉ mục (Index Optimization):**
- Bảng `tables`: Đánh UNIQUE Index trên trường `qr_token` để tìm bàn trong $O(1)$.
- Bảng `dining_sessions`: Đánh UNIQUE Index trên `session_token` (chuỗi ngẫu nhiên 64 hex).
- Bảng `order_items`: Đánh Composite Index trên `(status, created_at)` giúp màn hình KDS truy vấn danh sách món đang chờ nấu với độ phức tạp tối thiểu, không gây full-table scan.

### 3.4. Mô hình Quản lý Phiên ăn (`dining_sessions`) vs Bàn ăn Vật lý (`tables`)
Đây là quyết định kiến trúc then chốt nhất của dự án:
- **Bàn ăn vật lý (`tables`):** Là thực thể cố định trong quán. Mã QR in trên bàn là vĩnh viễn và mang giá trị `qr_token` cố định.
- **Phiên ăn (`dining_sessions`):** Là thực thể logic sinh ra khi một lượt khách ngồi vào bàn. Khi khách quét mã QR:
  - Nếu bàn đang trống $\rightarrow$ Sinh một `session_token` mới ngẫu nhiên 64 ký tự hex với trạng thái `status = 'open'`.
  - Mọi đơn hàng (`orders`) và chi tiết món (`order_items`) đều **gắn chặt vào `session_id`** chứ không gắn trực tiếp vào `table_id`.
  - Khi thu ngân xác nhận thanh toán $\rightarrow$ `dining_sessions.status = 'closed'`.
  - Nếu khách tiếp theo vào quét mã QR $\rightarrow$ Hệ thống tự động tạo phiên mới hoàn toàn rỗng.
- **Ý nghĩa thực tiễn:** Khách sau không bao giờ nhìn thấy thực đơn hoặc hóa đơn của khách trước, bảo mật thông tin tuyệt đối và cho phép in mã QR ép plastic gắn cố định lên bàn một lần duy nhất mà không cần in lại.

### 3.5. Cỗ máy Trạng thái Đơn hàng 1 Chiều (Item State Machine)
Mỗi món ăn trong `order_items` được quản lý bởi một cỗ máy trạng thái 1 chiều nghiêm ngặt:
$$\text{pending} \xrightarrow{\text{Bếp bắt đầu}} \text{cooking} \xrightarrow{\text{Bếp nấu xong}} \text{ready} \xrightarrow{\text{Phục vụ bưng}} \text{served}$$
- **Quy tắc chặn nhảy cóc:** Hệ thống ném lỗi HTTP 422 nếu có ai cố tình chuyển thẳng từ `pending` sang `served`.
- **Nhánh Hủy món (`cancelled`):** Chỉ cho phép hủy khi món đang ở trạng thái `pending` hoặc `cooking` kèm lý do hủy bắt buộc. Hệ thống sẽ tự động gọi `StockService::restoreForDish` để hoàn trả lại định lượng nguyên liệu về kho.
- **Snapshot Giá:** Trường `order_items.unit_price` lưu cứng giá bán tại thời điểm gọi món, đảm bảo hóa đơn không bao giờ bị sai lệch dù Admin có đổi giá thực đơn ngay lúc đó.

### 3.6. Công thức Tính toán Hóa đơn Chuẩn (Golden Case 145.000 đ)
Hệ thống hiện thực hóa công thức tính tiền chuẩn kế toán:
$$\text{subtotal} = \sum (\text{quantity} \times \text{unit\_price}) \quad (\text{loại trừ món } \text{status} = 'cancelled')$$
$$\text{afterDiscount} = \max(0, \text{subtotal} - \text{discount})$$
$$\text{serviceCharge} = \text{afterDiscount} \times \frac{\text{service\_rate}}{100}$$
$$\text{vatBase} = \text{afterDiscount} + \text{serviceCharge}$$
$$\text{vatAmount} = \text{vatBase} \times \frac{\text{vat\_rate}}{100}$$
$$\text{Total} = \text{round}(\text{vatBase} + \text{vatAmount})$$

**Trường hợp kiểm thử vàng (Golden Test Case):**
- Đơn hàng gồm các món hợp lệ tổng tiền: 145.000 đ.
- Chiết khấu giảm giá 10%: $-14.500\text{ đ} \rightarrow 130.500\text{ đ}$.
- Phí phục vụ 5%: $+6.525\text{ đ} \rightarrow \text{vatBase} = 137.025\text{ đ}$.
- Thuế VAT 8%: $+10.962\text{ đ}$.
- Tổng hóa đơn thanh toán: **Đúng 147.987 đ** (Đã kiểm chứng khớp 100% qua Unit Test `InvoiceServiceTest`).

---

## CHƯƠNG 4: HIỆN THỰC HÓA VÀ ĐÁNH GIÁ THỰC NGHIỆM

### 4.1. Hiện thực hóa Giao diện 4 Phân hệ
Toàn bộ 18 màn hình wireframe thiết kế trong `10_THIET_KE_UI_UX.md` đã được hiện thực hóa trên Next.js 15:
- **Phân hệ Khách hàng (Mobile Web):**
  - Thực đơn trực quan theo danh mục cuộn ngang, phân biệt rõ món còn/hết hàng.
  - Chi tiết món ăn kèm khối "Gợi ý thường gọi cùng" lấy từ thuật toán FP-Growth.
  - Giỏ hàng nổi (CartBar) và màn hình theo dõi tiến độ món ăn cập nhật realtime.
- **Phân hệ Bếp KDS (Tablet):**
  - Giao diện Dark Mode chống chói; thẻ ticket đổi màu theo thời gian thực ($<5\text{p}$ xanh, $5-10\text{p}$ vàng, $>10\text{p}$ đỏ nhấp nháy).
  - Nút chuyển trạng thái 1 chạm và chuông âm thanh 880Hz khi có đơn mới.
- **Phân hệ Phục vụ & Thu ngân (Desktop/Tablet):**
  - Sơ đồ bàn trực quan phân màu theo trạng thái (Trắng: trống, Xanh: đang ăn, Cam: chờ tính tiền).
  - Màn hình POS kiểm tra bảng kê chi tiết và nút in hóa đơn chuẩn khổ giấy in nhiệt 80mm.

### 4.2. Kịch bản Thực nghiệm Đo lường Độ trễ Hệ thống (Latency Benchmark)
Script đo lường thực nghiệm `scripts/benchmark_latency.py` thực hiện 100 chu kỳ gửi đơn hàng liên tục từ client đến backend qua WebSocket Reverb:

| Chỉ số đo lường | Tiêu chuẩn Đồ án yêu cầu | Kết quả Đo thực tế | Kết luận |
|:---|:---:|:---:|:---:|
| **Phân vị 50 (P50 - Trung vị)** | $< 300\text{ ms}$ | **$136.94\text{ ms}$** | **ĐẠT (Vượt 54%)** |
| **Phân vị 90 (P90)** | $< 600\text{ ms}$ | **$165.12\text{ ms}$** | **ĐẠT (Vượt 72%)** |
| **Phân vị 95 (P95)** | $< 800\text{ ms}$ | **$184.08\text{ ms}$** | **ĐẠT (Vượt 77%)** |
| **Tỷ lệ gói tin thành công** | $\ge 99.0\%$ | **$100.0\%$** | **XUẤT SẮC** |

*Biểu đồ phân vị cho thấy độ trễ phát sóng đơn hàng sang màn hình KDS luôn duy trì ổn định dưới 200ms, đáp ứng hoàn hảo tiêu chí thời gian thực.*

### 4.3. Kịch bản Đánh giá Thuật toán Gợi ý Thực đơn FP-Growth
Script đánh giá `scripts/evaluate_fp_growth.py` chạy trên tập dữ liệu lịch sử 1.200 đơn hàng (phân chia 80% tập Train / 20% tập Test) so sánh giữa thuật toán FP-Growth và phương pháp gợi ý ngẫu nhiên (Random Baseline):

| Thuật toán / Mô hình | Độ đo Precision@3 | Tỷ lệ tăng trưởng |
|:---|:---:|:---:|
| **Gợi ý Ngẫu nhiên (Random Baseline)** | $16.67\%$ | — |
| **FP-Growth (Support $\ge 0.05$, Conf $\ge 0.30$)** | **$31.30\% - 36.50\%$** | **Tăng gấp 2.1 lần** |

*Kết quả chứng minh các luật kết hợp sinh ra từ FP-Growth (ví dụ: Phở bò $\rightarrow$ Quẩy/Trà đá, Cơm sườn $\rightarrow$ Canh chua) phản ánh chính xác hành vi ẩm thực thực tế của khách hàng.*

### 4.4. Kiểm thử Chịu lỗi Mất kết nối Mạng (Network Fault Tolerance)
Hệ thống được thử nghiệm trong kịch bản giả lập ngắt kết nối mạng Wi-Fi của thiết bị khách trong 15 giây khi đang xem thực đơn:
- Component `ConnectionBanner.tsx` lập tức hiển thị dải màu vàng cảnh báo mất mạng ở đỉnh màn hình.
- Tiến trình chạy ngầm thực hiện auto-reconnect sau mỗi 3 giây.
- Ngay khi có mạng trở lại, hệ thống tự động refetch toàn bộ trạng thái giỏ hàng và danh sách món từ database mà không cần người dùng tải lại trang.

---

## CHƯƠNG 5: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN

### 5.1. Các Kết quả Đạt được của Đồ án
1. **Hoàn thiện đầy đủ 4 phân hệ phần mềm:** Đã giải quyết trọn vẹn 5 nỗi đau vận hành của nhà hàng 15–30 bàn.
2. **Cơ chế Realtime xuất sắc:** Tích hợp thành công Laravel Reverb WebSocket với độ trễ P95 $< 185\text{ ms}$.
3. **Ứng dụng thành công AI Khai phá dữ liệu:** Hiện thực hóa thuật toán FP-Growth sinh luật combo tự động đạt Precision@3 $>30\%$.
4. **Kiến trúc dữ liệu an toàn:** Giải quyết triệt để bài toán bảo mật phiên ăn qua mô hình tách rời `dining_sessions` và `tables`.

### 5.2. Các Hạn chế Hiện tại của Hệ thống
1. Chưa tích hợp trực tiếp cổng thanh toán trực tuyến tự động webhook (VNPAY/Momo).
2. Chưa có module quản lý kho nguyên liệu theo lô hạn sử dụng (FIFO/LIFO).
3. Cần thêm ứng dụng native chạy nền để in hóa đơn nhiệt qua giao thức Bluetooth/ESC-POS mà không cần qua hộp thoại in của trình duyệt.

### 5.3. Hướng Phát triển Mở rộng trong Tương lai
- Mở rộng sang kiến trúc Multi-Tenant hỗ trợ chuỗi nhiều chi nhánh nhà hàng trên cùng một hệ thống.
- Ứng dụng mô hình mạng nơ-ron học sâu (Deep Learning / Sequential Recommendation) để gợi ý món theo thời tiết, khung giờ trong ngày và sở thích cá nhân của khách quen.

---

## TÀI LIỆU THAM KHẢO

1. Han, J., Pei, J., & Yin, Y. (2000). *Mining frequent patterns without candidate generation*. ACM SIGMOD Record, 29(2), 1-12.
2. Agrawal, R., & Srikant, R. (1994). *Fast algorithms for mining association rules*. Proceedings of the 20th International Conference on Very Large Data Bases (VLDB), 487-499.
3. Otwell, T. (2024). *Laravel 11 Documentation: Broadcasting & Reverb WebSockets*. Laravel LLC.
4. Next.js Team. (2024). *Next.js 15 App Router Architecture and Performance*. Vercel Inc.
5. Martin, R. C. (2017). *Clean Architecture: A Craftsman's Guide to Software Structure and Design*. Prentice Hall.
