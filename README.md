# Hệ thống Gọi món tại Bàn (QR Order) và Quản lý Bếp Nhà hàng tích hợp Gợi ý Thực đơn (Combo)

> **Sinh viên thực hiện:** Hồ Văn Nhựt Cường
> **Học phần:** Đồ án 2 — năm học 2026–2027
> **Giá tham chiếu (theo file theo dõi):** 1500
> **Stack:** Laravel 11 API + Laravel Reverb (WebSocket) + Next.js 15 + MySQL 8 + Redis
> **Trạng thái:** hoàn chỉnh (12 file chi tiết)

---

## Đọc theo thứ tự này

| # | File | Nội dung |
| --- | ------ | ---------- |
| 1 | [01_TONG_QUAN_VA_PHAM_VI.md](document/01_TONG_QUAN_VA_PHAM_VI.md) | Bài toán, 5 actor, phạm vi MVP, ngoài phạm vi, giả định |
| 2 | [02_KIEN_TRUC_HE_THONG.md](document/02_KIEN_TRUC_HE_THONG.md) | Kiến trúc 4 khối, luồng QR→bếp→hóa đơn, WebSocket, quyết định kỹ thuật |
| 3 | [03_THIET_KE_CSDL.md](document/03_THIET_KE_CSDL.md) | Schema 16 bảng, index, ràng buộc, thứ tự migration |
| 4 | [04_NGHIEP_VU_DON_HANG_VA_HOA_DON.md](document/04_NGHIEP_VU_DON_HANG_VA_HOA_DON.md) | Vòng đời order item, tách/gộp bàn, giá, phụ phí, VAT, thanh toán |
| 5 | [05_WEBSOCKET_REALTIME_VA_GOI_Y_COMBO.md](document/05_WEBSOCKET_REALTIME_VA_GOI_Y_COMBO.md) | Reverb/Redis pub-sub + FP-Growth gợi ý món kèm |
| 6 | [06_API_VA_GIAO_DIEN.md](document/06_API_VA_GIAO_DIEN.md) | 42 endpoint, 18 màn hình (khách / bếp / admin) |
| 7 | [07_LO_TRINH_TRIEN_KHAI.md](document/07_LO_TRINH_TRIEN_KHAI.md) | 12 tuần, milestone, mốc an toàn ở tuần 8 |
| 8 | [08_KIEM_THU_VA_DANH_GIA.md](document/08_KIEM_THU_VA_DANH_GIA.md) | 35 test case, đo độ trễ WebSocket, đánh giá gợi ý |
| 9 | [09_RUI_RO_VA_PHUONG_AN.md](document/09_RUI_RO_VA_PHUONG_AN.md) | Rủi ro, 3 kịch bản phạm vi, ~18 câu hỏi phản biện |
| 10 | [10_THIET_KE_UI_UX.md](document/10_THIET_KE_UI_UX.md) | Triết lý thiết kế, Design System, UX flows, chi tiết 18 màn hình, Audio UX |
| 11 | [11_CHECKLIST_DU_AN.md](document/11_CHECKLIST_DU_AN.md) | Checklist toàn diện: môi trường, DB, API, KDS, test, demo & bảo vệ |
| 12 | [12_SLIDE_THUYET_TRINH_BAO_VE.md](document/12_SLIDE_THUYET_TRINH_BAO_VE.md) | Đề cương 15 Slide thuyết trình bảo vệ đồ án chuẩn thời lượng 15 phút |
| 13 | [13_BAO_CAO_DO_AN_TOT_NGHIEP.md](document/13_BAO_CAO_DO_AN_TOT_NGHIEP.md) | Toàn văn Cuốn Báo cáo Đồ án tốt nghiệp 5 chương học thuật chuẩn quy cách nộp khoa |

## Ba khối nghiệp vụ tách biệt

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│  KHÁCH (QR)     │      │  BẾP (KDS)      │      │  ADMIN          │
│  - quét QR bàn  │─────►│  - hàng đợi món │      │  - thực đơn     │
│  - gọi món      │ WS   │  - cập nhật     │ WS   │  - bàn/QR       │
│  - theo dõi     │◄────►│    trạng thái   │◄────►│  - hóa đơn      │
│  - gọi thêm     │      │  - báo hết món  │      │  - báo cáo      │
└─────────────────┘      └─────────────────┘      └─────────────────┘
         │                        │                        │
         └────────────────────────┴────────────────────────┘
                                  │
                      ┌───────────▼────────────┐
                      │  GỢI Ý COMBO (FP-Growth)│
                      │  khai phá từ lịch sử    │
                      └─────────────────────────┘
```

## Nguyên tắc xuyên suốt

1. **QR gắn theo phiên, không gắn theo bàn.** Mỗi lần khách ngồi sinh một `session` có token riêng — nhờ vậy 2 khách ngồi cùng bàn khác thời điểm không thấy order của nhau, và QR in sẵn trên bàn không bao giờ phải đổi.
2. **Giá món chốt tại thời điểm gọi.** `order_items.unit_price` là snapshot, không join sang `dishes` để lấy giá — thực đơn đổi giá không làm sai hóa đơn cũ.
3. **Trạng thái món là state machine có luật.** Không cho nhảy cóc `pending → served`; mọi chuyển trạng thái đi qua Service có kiểm tra và ghi log.
4. **WebSocket là kênh thông báo, không phải nguồn sự thật.** DB luôn đúng trước; nếu mất kết nối, client refetch là đủ.
5. **Gợi ý combo là tính năng phụ trợ.** Nếu không đủ dữ liệu, hệ thống vẫn chạy với combo do admin cấu hình tay.

## Ước lượng khối lượng

| Hạng mục | Số lượng |
| --- | --- |
| Bảng CSDL | 16 |
| API endpoint | ~42 |
| Màn hình Next.js | 18 (8 khách + 4 bếp + 6 admin) |
| Sự kiện WebSocket | 6 |
| Tuần triển khai | 12 |
| Test case nghiệp vụ | 35+ |
