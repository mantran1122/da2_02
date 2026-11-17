# 10 — Thiết kế giao diện và trải nghiệm người dùng (UI/UX Design Specification)

Tài liệu này đặc tả toàn diện triết lý thiết kế, Design System, cấu trúc layout, luồng trải nghiệm (UX Flows), micro-interactions và đặc tả chi tiết 18 màn hình cho 4 nhóm đối tượng: **Khách hàng (Mobile Web)**, **Bếp (Tablet KDS)**, **Phục vụ / Thu ngân (Tablet/Desktop)** và **Quản lý (Admin Dashboard)**.

---

## 1. Triết lý thiết kế & Nguyên tắc trải nghiệm (Core Principles)

Hệ thống phục vụ ngành dịch vụ ăn uống (F&B) với đặc thù vận hành nhanh, nhiều tiếng ồn, môi trường ẩm ướt và thao tác cường độ cao. Do đó, thiết kế tuân thủ 4 nguyên tắc cốt lõi:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   4 TRỤ CỘT TRẢI NGHIỆM NGƯỜI DÙNG                     │
├───────────────────┬───────────────────┬─────────────────┬──────────────┤
│ 1. ZERO-FRICTION  │ 2. GLANCEABLE     │ 3. 1-THUMB      │ 4. FAIL-SAFE │
│   (Khách gọi)     │    (KDS Bếp)      │    (Mobile Web) │    (Tất cả)  │
│ Không login/cài app│ Nhìn 1s hiểu ngay │ Nút chính nằm   │ Chặn bấm nhầm│
│ Vào là chọn món   │ Phân màu theo giờ │ trong vùng ngón │ Có xác nhận  │
│ Gửi bếp tức thì   │ Chữ to, tương phản│ cái với tới     │ Tự hồi phục  │
└───────────────────┴───────────────────┴─────────────────┴──────────────┘
```

1. **Zero-friction cho thực khách**: Khách quét QR là thấy menu ngay, không bắt tải app, không bắt đăng ký/đăng nhập, không đòi quyền truy cập vị trí/danh bạ. Số bước từ lúc quét mã tới lúc gửi đơn ≤ 3 bước.
2. **Glanceable UI cho Bếp (KDS)**: Màn hình bếp được quan sát từ khoảng cách 1 – 1.5 mét trong điều kiện ánh sáng phức tạp và dầu mỡ. Mọi thông tin phải nhận diện được bằng màu sắc (color coding), huy hiệu (badges) và số lượng to bản mà không cần đọc chữ nhỏ.
3. **One-Thumb Rule cho giao diện di động**: Các thành phần tương tác quan trọng (thêm vào giỏ, xem giỏ, gửi bếp, gọi phục vụ) luôn cố định ở nửa dưới màn hình (thumb zone), đảm bảo thao tác thoải mái bằng một tay khi đang cầm điện thoại.
4. **Fail-safe & Feedback tức thời**: Mọi hành vi quan trọng (hủy món, gộp bàn, thanh toán) đều có bước xác nhận rõ ràng với ngôn từ dễ hiểu. Mỗi thao tác đều có phản hồi xúc giác (haptic), âm thanh (audio) hoặc hiệu ứng thị giác (toast, badge animation).

---

## 2. Design System & Design Tokens

Hệ thống sử dụng **Tailwind CSS v3/v4** kết hợp với bộ component chuẩn **shadcn/ui** (Radix UI primitives).

### 2.1. Bảng màu (Color Palette & Semantic Tokens)

| Token | Mã màu (HEX) | Vai trò sử dụng |
| --- | --- | --- |
| `primary` | `#EA580C` (Orange-600) | Màu thương hiệu chủ đạo (kích thích vị giác, nút CTA chính, highlight) |
| `primary-hover` | `#C2410C` (Orange-700) | Trạng thái hover/active của nút chính |
| `secondary` | `#F97316` (Orange-500) | Badge danh mục, tag khuyến mãi, icon phụ trợ |
| `background` | `#F8FAFC` (Slate-50) | Nền tổng thể trang khách và admin (sáng sủa, sạch sẽ) |
| `surface` | `#FFFFFF` (White) | Nền thẻ món ăn, modal, drawer, sidebar |
| `text-primary`| `#0F172A` (Slate-900) | Tiêu đề, tên món, giá tiền (độ tương phản cao) |
| `text-muted` | `#64748B` (Slate-500) | Mô tả món ăn, đơn vị tính, thời gian phụ |
| `border` | `#E2E8F0` (Slate-200) | Đường phân cách nhẹ giữa các phần tử |

#### Bảng màu trạng thái món ăn & cảnh báo thời gian KDS

```
Trạng thái món:
  [pending]   xám       #64748B  (Chờ nhận đơn)
  [cooking]   cam ấm    #F97316  (Bếp đang nấu)
  [ready]     xanh dương#2563EB  (Món nấu xong, chờ bưng)
  [served]    xanh lá   #16A34A  (Đã ra bàn phục vụ khách)
  [cancelled] đỏ gạch   #DC2626  (Đã hủy món)

Cảnh báo thời gian chờ trên KDS:
  ● Xanh lá  (< 5 phút)  #16A34A | Nền nhạt #DCFCE7 | An toàn
  ▲ Vàng cam (5-10 phút) #D97706 | Nền nhạt #FEF3C7 | Cần chú ý làm nhanh
  ■ Đỏ nháy  (> 10 phút) #DC2626 | Nền nhạt #FEE2E2 | Trễ hẹn, ưu tiên số 1
```

### 2.2. Typography (Kiểu chữ & Cỡ chữ)

Sử dụng bộ font **Inter** hoặc **Plus Jakarta Sans** (hỗ trợ tiếng Việt hoàn hảo, bo tròn hiện đại, dễ đọc trên màn hình nhỏ).

| Cấp bậc | Cỡ chữ / Line Height | Trọng số (Weight) | Áp dụng cho |
| --- | --- | --- | --- |
| `Display` | 24px / 32px | Bold (700) | Tên bàn header khách, đồng hồ KDS |
| `Heading 1` | 20px / 28px | SemiBold (600) | Tiêu đề danh mục, tổng tiền hóa đơn |
| `Heading 2` | 16px / 24px | SemiBold (600) | Tên món ăn trên card, mã đơn hàng |
| `Body Large`| 16px / 24px | Regular (400) | Input form (chống auto-zoom trên iOS Safari) |
| `Body Small`| 14px / 20px | Regular (400) | Mô tả món, thành phần định lượng |
| `Caption` | 12px / 16px | Medium (500) | Badge trạng thái, thời gian chờ, ghi chú |

> **Quy tắc vàng iOS Safari**: Mọi thẻ `<input>` và `<textarea>` phải có `font-size: 16px` trở lên. Nếu dưới 16px, trình duyệt Safari trên iPhone sẽ tự động phóng to (zoom in) màn hình gây vỡ giao diện.

### 2.3. Quy chuẩn vùng chạm (Touch Target & Spacing)

- **Vùng bấm tối thiểu (Touch Target)**: $\ge 44 \times 44\text{ px}$ trên điện thoại khách, $\ge 48 \times 48\text{ px}$ trên màn hình bếp KDS để bấm chuẩn xác khi tay ướt hoặc đeo găng tay.
- **Bán kính góc bo (Border Radius)**:
  - `rounded-lg` (8px): Nút bấm, input field, chip trạng thái.
  - `rounded-xl` (12px): Thẻ món ăn (DishCard), Ticket bếp, Dialog.
  - `rounded-full` (9999px): Nút tăng/giảm số lượng (+/-), Floating Action Button (FAB).

---

## 3. Luồng trải nghiệm người dùng (UX Flows)

### 3.1. Luồng Khách hàng (Thực khách tại bàn)

```
[Khách đến bàn] ──► [Quét QR trên bàn]
                           │
                           ▼
                    [Mở Menu web] ◄────────────────────────────────┐
                           │                                       │
                ┌──────────┴───────────┐                           │
                ▼                      ▼                           │
        [Lướt theo danh mục]    [Tìm kiếm món]                     │
                │                      │                           │
                └──────────┬───────────┘                           │
                           ▼                                       │
              [Xem chi tiết + Gợi ý combo]                         │
                           │                                       │
                           ▼                                       │
                 [Thêm vào Giỏ hàng]                               │
                           │                                       │
                           ▼                                       │
               [Kiểm tra giỏ + Ghi chú món]                        │
                           │                                       │
                           ▼                                       │
               [Bấm "GỬI XUỐNG BẾP"]                               │
                           │                                       │
                           ▼                                       │
            [Theo dõi tiến độ món Realtime]                        │
            (Chờ ──► Đang nấu ──► Sắp ra ──► Đã ra)                │
                           │                                       │
           ┌───────────────┴─────────────────┐                     │
           ▼                                 ▼                     │
    [Muốn ăn thêm?] ─────────────────────────┴─────────────────────┘
           │ (Không)
           ▼
    [Bấm "Yêu cầu thanh toán"]
           │
           ▼
    [Thu ngân nhận thông báo ──► Khách xem Bill tạm]
           │
           ▼
    [Thanh toán xong ──► Xem Bill chính thức / Tải PDF / Đánh giá]
```

### 3.2. Luồng Bếp trưởng / Nhân viên bếp (KDS)

```
[Màn hình KDS mở sẵn]
        │
        ▼
[Có đơn mới từ khách] ──► [Phát chuông "Ding" + Thẻ rung nhẹ xuất hiện cột trái]
        │
        ├───────────────────────────────────────────────────────┐
        ▼ (Bình thường)                                         ▼ (Hết nguyên liệu)
[Bếp ấn "BẮT ĐẦU NẤU"]                                  [Ấn "HỦY MÓN"]
        │ (Món chuyển màu cam #F97316)                          │
        ▼                                                       ▼
[Bếp nấu xong, ấn "SẴN SÀNG"]                           [Chọn lý do trong modal]
        │ (Món chuyển màu xanh dương #2563EB)                   │
        ▼                                                       ▼
[Phục vụ bưng ra bàn ──► Ấn "ĐÃ RA BÀN"]                [Hệ thống hoàn tồn kho]
        │ (Món biến mất khỏi KDS hoặc sang Tab Đã xong)         [Bắn thông báo huỷ tới khách]
```

---

## 4. Đặc tả chi tiết 18 Màn hình giao diện

### Phân hệ 1: Khách hàng (Mobile Web Responsive — 8 màn hình)

#### 1. Màn hình Thực đơn chính (`/s/[tableToken]`)
- **Header cố định (Sticky Top)**:
  - Tên quán ("Quán Bếp Nhỏ"), Tên bàn ("Bàn B12 — Tầng 1").
  - Nút tìm kiếm (kính lúp) và nút chuông gọi phục vụ.
- **Thanh danh mục (Horizontal Scroll Tab)**:
  - Hiển thị danh mục: `Tất cả`, `Khai vị`, `Món chính`, `Món lẩu`, `Đồ uống`, `Tráng miệng`.
  - Tự động active tab khi cuộn màn hình tới phần danh mục tương ứng (Scrollspy).
- **Danh sách món ăn (Dish List)**:
  - Layout: Thẻ ngang (2 cột trên mobile: ảnh vuông $80\times 80\text{ px}$ bên trái, thông tin bên phải).
  - Tên món (đậm, tối đa 2 dòng), mô tả ngắn 1 dòng, giá tiền định dạng `60.000 đ`.
  - Nút bấm `+` tròn cam lớn ($36\times 36\text{ px}$) góc dưới bên phải card.
  - Nếu món hết hàng: Ảnh phủ lớp mờ xám (opacity 50%), hiện badge `Hết món`, nút `+` bị disable.
- **Thanh Giỏ hàng nổi (Floating Cart Bar — Sticky Bottom)**:
  - Luôn hiện ở đáy màn hình khi giỏ có $\ge 1$ món.
  - Icon giỏ hàng + badge số lượng món đỏ + tổng tiền tạm tính.
  - Nút CTA "Xem giỏ hàng" màu cam nổi bật.

```
┌───────────────────────────────────────────────┐
│ 🏠 Quán Bếp Nhỏ         📍 Bàn B12 · Tầng 1  🔍│
├───────────────────────────────────────────────┤
│ [ Tất cả ] [ Khai vị ] [ Món chính ] [ Nước ] │
├───────────────────────────────────────────────┤
│ ┌──────┐  Phở Bò Đặc Biệt            65.000 đ │
│ │ Ảnh  │  Bò tái, nạm, gầu, bò viên            │
│ │      │  ⏱ 8-10 phút                    [ + ] │
│ └──────┘                                      │
│ ┌──────┐  Bún Chả Hà Nội             50.000 đ │
│ │ Ảnh  │  Chả nướng than hoa, nem cua          │
│ │      │  ⏱ 5-7 phút                     [ + ] │
│ └──────┘                                      │
│ ┌──────┐  Nem Rán Giòn (4 cái)       35.000 đ │
│ │ [HẾT]│  (Tạm hết nguyên liệu)                │
│ └──────┘                             [ Hết ]  │
├───────────────────────────────────────────────┤
│ 🛒 3 món · 150.000 đ            [ XEM GIỎ ➔ ] │
└───────────────────────────────────────────────┘
```

#### 2. Màn hình Chi tiết món & Gợi ý Combo (`/s/[tableToken]/dish/[id]`)
- Ảnh bìa món ăn tỉ lệ $16:9$, chất lượng cao, có nút back góc trái.
- Chi tiết món: Tên, giá, thời gian chế biến dự kiến, thông tin dinh dưỡng / nguyên liệu chính.
- Bộ chọn số lượng: Nút `[-]`, ô số lượng, nút `[+]`.
- Ô nhập ghi chú nhanh: Chips gợi ý sẵn ("Không hành", "Ít cay", "Nhiều đá", "Để riêng nước chấm") + ô Textarea tự nhập.
- **Khối Gợi ý Combo (FP-Growth Recommendation)**:
  - Tiêu đề: *"Thường được gọi cùng"* kèm icon gợi ý thông minh ⭐.
  - Băng chuyền ngang (Carousel) gồm 3 món có điểm confidence cao nhất.
  - Mỗi món gợi ý có ảnh nhỏ, tên, giá và nút "+ Thêm nhanh".
- Nút Action đáy màn hình: "Thêm vào giỏ hàng • 65.000 đ".

#### 3. Màn hình Giỏ hàng tạm (`/s/[tableToken]/cart`)
- Danh sách món trong giỏ:
  - Cho phép tăng giảm số lượng từng món. Nếu giảm về 0 hiện dialog xác nhận xóa.
  - Ghi chú món kèm icon bút chỉnh sửa.
- Ghi chú chung cho toàn bộ đơn hàng (ví dụ: "Cho xin trước chén đũa").
- Bảng tóm tắt tạm tính:
  - Tiền món ăn: `subtotal`
  - Ước tính phụ phí (nếu có cấu hình).
- Nút CTA chính: **"GỬI YÊU CẦU XUỐNG BẾP"** (To bản, hiệu ứng rung xác nhận khi bấm).

#### 4. Màn hình Theo dõi đơn hàng Realtime (`/s/[tableToken]/orders`)
- Hiển thị danh sách tất cả các đợt gọi món của bàn trong phiên hiện tại (Lần 1: 11:30, Lần 2: 11:45).
- Mỗi món hiển thị thanh tiến độ ngang hoặc Chip trạng thái màu sắc:
  - `⏳ Đã gửi bếp` $\rightarrow$ `🔥 Đang nấu` $\rightarrow$ `🔔 Sắp ra bàn` $\rightarrow$ `✅ Đã phục vụ`.
- Nếu món ở trạng thái `pending`, hiển thị nút text nhỏ: *"Hủy món"*. Khi bấm mở modal hỏi lý do hủy.
- Nút nổi phía dưới: *"Gọi thêm món khác"* $\rightarrow$ quay lại menu chính giữ nguyên phiên.

#### 5. Màn hình Hóa đơn tạm tính & Thanh toán (`/s/[tableToken]/bill`)
- Bảng kê chi tiết từng món đã phục vụ (không tính các món bị hủy).
- Số tiền rõ ràng: Tiền món, Phí phục vụ (nếu có), Thuế VAT (8%), Tổng cần thanh toán.
- Nút **"Yêu cầu nhân viên thanh toán"**:
  - Khi bấm, nút chuyển sang trạng thái "Đã gọi nhân viên • Đang đến", đồng thời gửi WebSocket sự kiện `PaymentRequested` tới quầy thu ngân.
  - Hiển thị mã QR ngân hàng chuyển khoản kèm số tài khoản và cú pháp chuyển khoản (Mã phiên + Số bàn).

#### 6. Màn hình Hóa đơn đã thanh toán (`/s/[tableToken]/bill/paid`)
- Thông báo "Thanh toán thành công. Cảm ơn quý khách!".
- Bản tóm tắt hóa đơn có mã hóa đơn, ngày giờ, phương thức thanh toán.
- Nút **"Tải hóa đơn điện tử (PDF)"**.
- Nút chuyển tiếp tới màn hình Đánh giá dịch vụ.

#### 7. Màn hình Tìm kiếm món ăn (`/s/[tableToken]/search`)
- Thanh input auto-focus với nút xóa nhanh `x`.
- Lịch sử tìm kiếm gần đây + Từ khóa hot ("phở bò", "trà tắc", "nem chua rán").
- Kết quả tìm kiếm trả về tức thì sau 300ms debounce.

#### 8. Màn hình Đánh giá bữa ăn (`/s/[tableToken]/feedback`)
- Chấm điểm số sao (1 – 5 sao) cho: Chất lượng món ăn, Tốc độ phục vụ, Không gian quán.
- Ô nhập cảm nghĩ / đóng góp ý kiến.
- Nút gửi đánh giá hoàn tất phiên.

---

### Phân hệ 2: Bếp — KDS (Kitchen Display System — Tablet Landscape — 4 màn hình)

Thiết kế tối ưu cho màn hình Tablet $10 - 12\text{ inch}$ đặt nằm ngang (Landscape), chạy chế độ toàn màn hình (Fullscreen PWA).

#### 9. Màn hình KDS Hàng đợi chính (`/kitchen`)
- **Thanh Header trên cùng**:
  - Đồng hồ thời gian thực (`14:32:05`).
  - Thống kê nhanh: `Chờ nấu: 8` | `Đang nấu: 3` | `TB chờ: 6.2 phút`.
  - Nút bật/tắt âm thanh cảnh báo 🔔.
  - Bộ lọc danh mục bếp (VD: Bếp Nấu, Bếp Nướng, Quầy Pha chế).
- **Khu vực hiển thị thẻ Order (Ticket Grid)**:
  - Chia 4 hoặc 5 cột tùy kích thước màn hình.
  - Các thẻ sắp xếp theo thứ tự FIFO (món nào gọi trước nằm trên cùng bên trái).
  - Cấu trúc 1 Ticket:
    - **Header Ticket**: Số bàn (VD: `B12`), Giờ gọi (`14:26`), Huy hiệu đếm phút trôi qua (`+6p`).
    - **Màu viền Ticket**:
      - Xanh lá nếu $< 5$ phút.
      - Vàng nếu $5 - 10$ phút.
      - Đỏ kèm nhấp nháy chậm nếu $> 10$ phút.
    - **Danh sách món trong Ticket**:
      - Tên món (chữ đậm $18\text{ px}$), Số lượng to trong khung vuông (VD: `x2`).
      - Ghi chú món in màu đỏ/vàng nổi bật (VD: *"Không bỏ hành, ít ớt"*).
      - Nút Action 1 chạm duy nhất trên mỗi dòng món:
        - Món `pending`: Hiện nút cam **[Nấu]**.
        - Món `cooking`: Hiện nút xanh lá **[Xong]**.
        - Sau khi bấm [Xong], dòng món mờ đi và chuyển sang hàng chờ bưng.
    - **Nút Hủy món**: Icon thùng rác nhỏ góc thẻ dành cho trường hợp sự cố.

```
┌───────────────────────────────────────────────────────────────────────────────┐
│ 👨‍🍳 KDS BẾP — 14:32:10    [Chờ: 8]  [Đang nấu: 3]  [TB: 6.2p]    [🔔 Âm thanh: BẬT]│
├─────────────────┬─────────────────┬─────────────────┬─────────────────────────┤
│ BÀN B12   ⏱ 6p  │ BÀN B08   ⏱ 3p  │ BÀN B15   ⏱ 1p  │ BÀN B03   ⏱ 12p (TRỄ)   │
│ Viền: VÀNG      │ Viền: XANH      │ Viền: XANH      │ Viền: ĐỎ NHẤP NHÁY      │
├─────────────────┼─────────────────┼─────────────────┼─────────────────────────┤
│ Phở Bò Tái  × 2 │ Cơm Gà Xối Mỡ ×1│ Mì Xào Bò   × 2 │ Canh Chua Cá   × 1      │
│ ⚠ Ghi chú:      │                 │                 │ ⚠ Ít cay, chua nhiều    │
│  "Không hành"   │                 │                 │                         │
│ [ BẮT ĐẦU NẤU ] │ [ BẮT ĐẦU NẤU ] │ [ BẮT ĐẦU NẤU ] │ [ HOÀN TẤT MÓN ]        │
│ ─────────────── │ ─────────────── │ ─────────────── │ ─────────────────────── │
│ Trà Đá      × 2 │ Canh Rong Biển×1│ Coca Lon    × 2 │ Cơm Trắng      × 2      │
│ [ HOÀN TẤT ]    │ [ BẮT ĐẦU NẤU ] │ [ HOÀN TẤT ]    │ [ HOÀN TẤT ]            │
└─────────────────┴─────────────────┴─────────────────┴─────────────────────────┘
```

#### 10. Màn hình Lịch sử món đã nấu (`/kitchen/history`)
- Bảng danh sách các món đã hoàn thành trong ca làm việc hôm nay.
- Cột: Tên món, Bàn, Giờ gọi, Giờ xong, Tổng thời gian chế biến.
- Cho phép phục hồi lại trạng thái món nếu nhân viên bếp lỡ tay bấm nhầm [Xong].

#### 11. Màn hình Báo hết món nhanh (`/kitchen/stock`)
- Danh sách dạng lưới lớn (Grid cards) của tất cả các món ăn trong thực đơn.
- Mỗi thẻ gồm ảnh món, tên món và 1 nút Toggle cực lớn: **"CÒN MÓN" (Xanh)** / **"BÁO HẾT" (Đỏ)**.
- Khi bếp bật sang "BÁO HẾT", hệ thống ngay lập tức broadcast WebSocket `DishOutOfStock` xuống thực đơn của tất cả khách hàng đang quét mã.

#### 12. Màn hình Thống kê hiệu suất bếp (`/kitchen/stats`)
- Biểu đồ số lượng món chế biến theo từng khung giờ trong ngày.
- Top 5 món nấu lâu nhất (vượt quá `estimated_prep_time`).
- Tỷ lệ món đúng hạn / món trễ hẹn.

---

### Phân hệ 3: Phục vụ & Thu ngân (POS Tablet/Desktop — 4 màn hình)

#### 13. Màn hình Sơ đồ bàn trực quan (`/staff/tables`)
- Bộ lọc theo khu vực dạng Tabs: `Tất cả bàn`, `Tầng 1 (10 bàn)`, `Sân vườn (8 bàn)`, `Phòng VIP (4 bàn)`.
- Sơ đồ trực quan dạng Grid thẻ bàn:
  - **Bàn trống (Trắng / Viền xám)**: Hiện số bàn, số ghế, nút "Mở phiên thủ công".
  - **Bàn đang ăn (Xanh lá)**: Hiện số bàn, thời gian khách ngồi (`45p`), số món đang phục vụ, tổng tiền tạm tính.
  - **Bàn chờ thanh toán (Cam nhấp nháy)**: Có icon chuông tiền `$` khi khách ấn yêu cầu thanh toán trên app.
- Khi click vào bàn: Mở Drawer / Modal tóm tắt thông tin phiên ăn.

```
┌────────────────────────────────────────────────────────────────────────┐
│ 🍽 SƠ ĐỒ BÀN           [Tất cả: 22]  [Trống: 12]  [Đang ăn: 8]  [Chờ bill: 2] │
├────────────────────────────────────────────────────────────────────────┤
│ Khu vực: [ Tầng 1 ]  [ Sân vườn ]  [ VIP Lầu 1 ]                       │
├──────────────┬──────────────┬──────────────┬──────────────┬────────────┤
│ BÀN B01 (4)  │ BÀN B02 (4)  │ BÀN B03 (2)  │ BÀN B04 (6)  │ BÀN B05 (4)│
│ [ TRỐNG ]    │ 🟢 Đang ăn   │ 🟡 Chờ bill  │ 🟢 Đang ăn   │ [ TRỐNG ]  │
│              │ 35p · 240k   │ 52p · 410k   │ 15p · 120k   │            │
│ [+ Mở bàn]   │ [Xem chi tiết]│ [Tính tiền]  │ [Xem chi tiết]│ [+ Mở bàn] │
└──────────────┴──────────────┴──────────────┴──────────────┴────────────┘
```

#### 14. Màn hình Chi tiết phiên ăn & Thao tác bàn (`/staff/sessions/[id]`)
- Thông tin chi tiết: Giờ vào, danh sách đơn hàng đã gọi, trạng thái từng món.
- Các nút chức năng nghiệp vụ đặc thù:
  - **Chuyển bàn**: Chọn bàn đích trống $\rightarrow$ Chuyển toàn bộ món và phiên sang bàn mới.
  - **Gộp bàn**: Chọn bàn đang có khách khác $\rightarrow$ Hợp nhất 2 phiên thành 1.
  - **Thêm món hộ khách**: Dành cho khách lớn tuổi không rành điện thoại thông minh.

#### 15. Màn hình Danh sách hóa đơn (`/cashier/invoices`)
- Bảng quản lý hóa đơn theo ngày: Mã hóa đơn, Bàn, Giờ mở, Giờ đóng, Tổng tiền, Trạng thái (`draft`, `paid`, `cancelled`).
- Bộ lọc theo trạng thái và thanh tìm kiếm theo mã hóa đơn hoặc số bàn.

#### 16. Màn hình Thanh toán & Xuất hóa đơn (`/cashier/invoices/[id]`)
- Bố cục chia 2 phần (Split View):
  - **Cột trái**: Bảng kê chi tiết món ăn (số lượng, đơn giá chốt, thành tiền).
  - **Cột phải**: Khối tính toán tiền tệ:
    - Tiền hàng (Subtotal).
    - Ô nhập chiết khấu / giảm giá (theo `%` hoặc trừ trực tiếp số tiền mặt).
    - Phí phục vụ tự động tính (nếu cấu hình $> 0$).
    - Thuế VAT tự động tính trên tổng sau phí.
    - **Tổng cộng cần thu** (Font to bản $28\text{ px}$, màu xanh nổi bật).
    - Phương thức thanh toán: `Tiền mặt` / `Chuyển khoản QR` / `Thẻ`.
    - Ô tính tiền thừa trả khách khi khách đưa tiền mặt.
  - Nút Action: **"XÁC NHẬN THANH TOÁN & IN HÓA ĐƠN"** (Tự động mở popup in PDF và đóng phiên).

---

### Phân hệ 4: Quản trị viên (Admin Dashboard — 2 màn hình trọng tâm)

#### 17. Màn hình Quản lý Thực đơn & Định lượng (`/admin/dishes`)
- Bảng danh sách món ăn phân trang: Ảnh thumbnail, Tên món, Danh mục, Đơn giá, Thời gian nấu dự kiến, Tồn kho hiện tại.
- Modal Form Thêm/Sửa món:
  - Upload ảnh (drag & drop, preview, validate dung lượng $< 2\text{MB}$).
  - Nhập tên món, chọn danh mục, đơn giá, thời gian nấu ước tính.
  - **Tab Định lượng nguyên liệu (Recipe Specification)**:
    - Bảng ghép nguyên liệu tiêu hao: Chọn nguyên liệu (từ bảng `ingredients`), nhập số lượng định mức (VD: 150 gram thịt bò, 200 gram bánh phở tươi).
    - Giúp hệ thống tự động tính số phần ăn còn lại tối đa (`maxPortions`).

#### 18. Màn hình Quản lý & Khai phá Luật Combo (`/admin/combo-rules`)
- Bảng quản lý các luật gợi ý combo:
  - Món gốc (Antecedent) $\rightarrow$ Món gợi ý kèm (Consequent).
  - Nguồn gốc luật: `manual` (Admin cấu hình tay) hoặc `fp_growth` (Hệ thống AI tự khai phá).
  - Các chỉ số thống kê toán học: `Support` (Độ hỗ trợ), `Confidence` (Độ tin cậy), `Lift` (Mức độ tương quan).
  - Trạng thái kích hoạt (Toggle `is_active`).
- Thanh công cụ hành động:
  - Nút thêm luật thủ công.
  - Nút **"Chạy lại mô hình FP-Growth ngay"**: Kích hoạt Queue Job quét lại toàn bộ dữ liệu lịch sử gọi món để cập nhật danh sách combo mới nhất.

---

## 5. Trạng thái giao diện đặc thù & Xử lý ngoại lệ (States & Fallbacks)

Một giao diện chất lượng cao phải xử lý mượt mà mọi trạng thái trung gian chứ không chỉ có màn hình thành công:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CÁC TRẠNG THÁI GIAO DIỆN                        │
├───────────────────┬───────────────────┬────────────────────────────────┤
│ Trạng thái        │ Biểu hiện UI      │ Hành vi xử lý                  │
├───────────────────┼───────────────────┼────────────────────────────────┤
│ 1. Empty State    │ Hình vẽ minh họa  │ Nêu rõ lý do trống + nút gợi ý │
│    (Trống dữ liệu)│ vector + thông điệp│ VD: "Giỏ hàng đang trống" ──►  │
│                   │ thân thiện        │ Nút "Khám phá món ngon ngay"   │
├───────────────────┼───────────────────┼────────────────────────────────┤
│ 2. Loading State  │ Skeleton Loader   │ Không dùng spinner toàn trang; │
│    (Đang tải)     │ (Khung xám mờ     │ Dùng khung xương khớp layout để│
│                   │  nhấp nháy)       │ tạo cảm giác tải trang siêu tốc│
├───────────────────┼───────────────────┼────────────────────────────────┤
│ 3. Offline / Mất  │ Banner màu vàng   │ Tự động reconnect sau mỗi 3s;  │
│    kết nối WS     │ cố định trên đỉnh │ Nút bấm "Thử kết nối lại ngay" │
│                   │ màn hình          │ Tự refetch dữ liệu khi có mạng │
├───────────────────┼───────────────────┼────────────────────────────────┤
│ 4. Hết món khi    │ Toast đỏ + rung   │ Đánh dấu đỏ món bị hết trong   │
│    đang gửi đơn   │ Modal giải thích  │ giỏ, giữ nguyên các món còn lại│
└───────────────────┴───────────────────┴────────────────────────────────┘
```

### Chiến lược Optimistic UI vs Pessimistic UI
- **Thêm món vào giỏ hàng**: Dùng **Optimistic UI** — Khi khách bấm `+`, số trên giỏ tăng ngay lập tức trong 0ms mà không đợi server phản hồi.
- **Gửi đơn xuống Bếp (`POST /s/orders`)**: Dùng **Pessimistic UI** — Hiển thị hiệu ứng Loading trên nút "Gửi bếp", chỉ thông báo thành công sau khi Database đã hoàn tất Transaction và trừ kho an toàn. Điều này ngăn chặn tình trạng khách tưởng đơn đã gửi nhưng backend bị lỗi tồn kho.

---

## 6. Phản hồi âm thanh (Audio UX) & Xúc giác (Haptic Feedback)

Trong không gian nhà hàng nhộn nhịp, phản hồi bằng âm thanh và độ rung đóng vai trò quyết định đến hiệu suất vận hành:

1. **Âm thanh chuông Order KDS (`ding-order.mp3`)**:
   - Tần số: $880\text{ Hz}$ (nốt A5) ngân vang trong 0.8 giây, âm lượng chuẩn hóa $75\text{ dB}$.
   - Tự động kích hoạt khi nhận WebSocket event `OrderCreated`.
   - Có cơ chế xin quyền Autoplay của trình duyệt khi nhân viên bếp lần đầu chạm vào màn hình.
2. **Âm thanh cảnh báo trễ hẹn KDS (`alert-timeout.mp3`)**:
   - Chuông kép nhịp nhanh khi có đơn hàng vượt quá 10 phút chưa nấu xong.
3. **Phản hồi rung điện thoại khách (Vibration API)**:
   - Khi món ăn chuyển sang trạng thái `ready` (sắp bưng ra bàn), điện thoại khách rung nhẹ theo mẫu: `navigator.vibrate([100, 50, 100])` để khách chuẩn bị đón món.

---

## 7. Tiêu chuẩn tiếp cận & Khả năng sử dụng (A11y & Usability)

- **Độ tương phản màu sắc**: Đạt chuẩn **WCAG 2.1 Level AA** (tỉ lệ tương phản giữa chữ và nền tối thiểu $4.5:1$ đối với văn bản thông thường và $3:1$ đối với văn bản to bản).
- **Hỗ trợ chế độ nền tối (Dark Mode) cho KDS**: Môi trường bếp làm việc ca tối thường có ánh sáng gắt từ đèn sưởi thức ăn; giao diện Dark Mode giúp giảm mỏi mắt cho đầu bếp.
- **Phím tắt hỗ trợ cho quầy Thu ngân**:
  - `F1`: Về sơ đồ bàn.
  - `F2`: Tìm kiếm hóa đơn.
  - `F9`: Xác nhận thanh toán nhanh bằng tiền mặt.
  - `Esc`: Đóng các cửa sổ Popup / Modal.

---

## 8. Sơ đồ cây điều hướng giao diện (Information Architecture Sitemap)

```
[HỆ THỐNG GỌI MÓN VÀ QUẢN LÝ BẾP]
│
├── 📱 GIAO DIỆN KHÁCH HÀNG (/s/[tableToken])
│   ├── Thực đơn chính & Giỏ hàng nổi (Menu & Floating Cart)
│   ├── Chi tiết món ăn & Gợi ý Combo (Dish Details & Upsell)
│   ├── Xem giỏ & Ghi chú món (Cart Review & Notes)
│   ├── Theo dõi trạng thái món Realtime (Live Order Tracking)
│   ├── Hóa đơn tạm & Gọi thanh toán (Pre-bill & Payment Request)
│   ├── Hóa đơn chính thức & Tải PDF (Paid Invoice & PDF Export)
│   ├── Tìm kiếm món ăn (Search Dishes)
│   └── Đánh giá chất lượng dịch vụ (Feedback Form)
│
├── 👨‍🍳 GIAO DIỆN BẾP - KDS (/kitchen)
│   ├── Hàng đợi món Realtime theo FIFO (Live Cooking Queue)
│   ├── Lịch sử món đã nấu trong ngày (Kitchen History)
│   ├── Bật/Tắt báo hết món nhanh 1 chạm (Quick Stock Toggle)
│   └── Thống kê thời gian chế biến & Hiệu suất (Kitchen Stats)
│
├── 💁 GIAO DIỆN PHỤC VỤ & THU NGÂN (/staff & /cashier)
│   ├── Sơ đồ bàn trực quan theo khu vực (/staff/tables)
│   ├── Chi tiết phiên, Chuyển bàn, Gộp bàn (/staff/sessions/[id])
│   ├── Danh sách hóa đơn (/cashier/invoices)
│   └── Thu ngân POS, Áp giảm giá, Thu tiền, In bill (/cashier/invoices/[id])
│
└── ⚙️ GIAO DIỆN QUẢN TRỊ ADMIN (/admin)
    ├── Quản lý món ăn & Định lượng nguyên liệu (/admin/dishes)
    └── Quản lý & Khai phá luật Combo FP-Growth (/admin/combo-rules)
```
