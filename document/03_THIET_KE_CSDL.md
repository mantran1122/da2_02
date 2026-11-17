# 03 — Thiết kế cơ sở dữ liệu

MySQL 8, charset `utf8mb4_unicode_ci`, engine InnoDB. Tất cả bảng có `id BIGINT UNSIGNED AUTO_INCREMENT`, `created_at`, `updated_at`.

## 1. Sơ đồ quan hệ

```
restaurants 1──n areas 1──n tables 1──n dining_sessions
                                                  │
                                                  ├──n orders 1──n order_items n──1 dishes
                                                  │                │
                                                  │                └──1 invoice_items
                                                  └──1 invoices 1──n invoice_items
                                                             └──n payments

categories 1──n dishes n──n ingredients (qua dish_ingredients)
dishes 1──n combo_rules (antecedent) / combo_rules (consequent)
staff n──1 restaurants
```

## 2. Bảng `restaurants` — nhà hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| name | VARCHAR(150) | NOT NULL |  |
| address | VARCHAR(255) | NOT NULL |  |
| phone | VARCHAR(15) | NULL |  |
| vat_percent | DECIMAL(5,2) | NOT NULL DEFAULT 8.00 |  |
| service_charge_percent | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | phí phục vụ |
| open_hours | VARCHAR(50) | NULL | vd '10:00-22:00' |

> Với đồ án chỉ có 1 nhà hàng, nhưng vẫn giữ bảng để cấu hình VAT/phí phục vụ tập trung.

## 3. Bảng `staff` — nhân viên

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| restaurant_id | BIGINT UNSIGNED | FK → restaurants.id, NOT NULL |  |
| name | VARCHAR(120) | NOT NULL |  |
| username | VARCHAR(50) | UNIQUE, NOT NULL |  |
| password | VARCHAR(255) | NOT NULL | bcrypt |
| role | ENUM('admin','waiter','cashier','kitchen') | NOT NULL |  |
| is_active | TINYINT(1) | NOT NULL DEFAULT 1 |  |

## 4. Bảng `areas` — khu vực

| Cột | Kiểu | Ràng buộc |
| --- | --- | --- |
| id | BIGINT UNSIGNED | PK |
| restaurant_id | BIGINT UNSIGNED | FK → restaurants.id, NOT NULL |
| name | VARCHAR(100) | NOT NULL |
| sort_order | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 |

## 5. Bảng `tables` — bàn ăn

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| restaurant_id | BIGINT UNSIGNED | FK → restaurants.id, NOT NULL |  |
| area_id | BIGINT UNSIGNED | FK → areas.id, NOT NULL |  |
| code | VARCHAR(20) | NOT NULL | vd 'B12' |
| seats | TINYINT UNSIGNED | NOT NULL DEFAULT 4 |  |
| qr_token | CHAR(36) | UNIQUE, NOT NULL | UUID in trên QR, **cố định vĩnh viễn** |
| status | ENUM('vacant','occupied','reserved','cleaning') | NOT NULL DEFAULT 'vacant' |  |
| position_x | SMALLINT | NULL | toạ độ trên sơ đồ |
| position_y | SMALLINT | NULL |  |

**Unique:** `(restaurant_id, code)`.

> **`qr_token` không bao giờ đổi.** QR in trên bàn dùng mãi. Việc phân biệt khách nằm ở `dining_sessions.token`, không ở QR.

## 6. Bảng `dining_sessions` — phiên ăn ⭐

Đây là **bảng quan trọng nhất** của thiết kế. Mỗi lần khách ngồi là một phiên.

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| table_id | BIGINT UNSIGNED | FK → tables.id, NOT NULL |  |
| token | CHAR(64) | UNIQUE, NOT NULL | random 32 byte hex — định danh khách |
| guest_count | TINYINT UNSIGNED | NOT NULL DEFAULT 1 | số khách |
| opened_at | DATETIME | NOT NULL |  |
| closed_at | DATETIME | NULL | NULL = đang mở |
| status | ENUM('open','awaiting_payment','paid','closed','cancelled') | NOT NULL DEFAULT 'open' |  |
| opened_by | BIGINT UNSIGNED | NULL, FK → staff.id | NULL = khách tự mở qua QR |
| note | TEXT | NULL |  |

**Index:** `(table_id, status)` — tìm phiên đang mở của bàn.

**Ràng buộc nghiệp vụ:** một bàn chỉ có tối đa **1 phiên `open`/`awaiting_payment`** tại một thời điểm. Kiểm tra ở `SessionService`, và có thể thêm partial unique index nếu MySQL hỗ trợ (hoặc dùng unique trên cột sinh `open_table_id`).

### 6.1. Vì sao cần phiên?

| Tình huống | Không có phiên | Có phiên |
| --- | --- | --- |
| Khách A ăn xong rời đi, khách B ngồi vào bàn 12 | Khách B thấy order của A | Khách B mở phiên mới, không thấy gì |
| Hai nhóm khách dùng chung bàn | Lẫn order | Mỗi nhóm vào phiên riêng (nếu bàn đủ chỗ — hoặc gộp theo nghiệp vụ) |
| QR in trên bàn | Phải in lại mỗi lần khách đổi | In 1 lần dùng mãi |
| Báo cáo doanh thu | Không biết lượt khách nào với lượt nào | Nhóm được theo phiên |

## 7. Bảng `categories` — danh mục món

| Cột | Kiểu | Ràng buộc |
| --- | --- | --- |
| id | BIGINT UNSIGNED | PK |
| restaurant_id | BIGINT UNSIGNED | FK → restaurants.id, NOT NULL |
| name | VARCHAR(100) | NOT NULL |
| sort_order | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 |
| is_active | TINYINT(1) | NOT NULL DEFAULT 1 |

## 8. Bảng `dishes` — món ăn

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| category_id | BIGINT UNSIGNED | FK → categories.id, NOT NULL |  |
| name | VARCHAR(150) | NOT NULL |  |
| description | TEXT | NULL |  |
| price | BIGINT UNSIGNED | NOT NULL | đồng |
| image_path | VARCHAR(255) | NULL |  |
| unit | VARCHAR(30) | NOT NULL DEFAULT 'phần' | 'phần', 'ly', 'dĩa' |
| estimated_prep_time | SMALLINT UNSIGNED | NOT NULL DEFAULT 15 | phút — dùng cho cảnh báo KDS |
| status | ENUM('available','out_of_stock','hidden') | NOT NULL DEFAULT 'available' |  |
| sort_order | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 |  |

## 9. Bảng `ingredients` — nguyên liệu

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| restaurant_id | BIGINT UNSIGNED | FK → restaurants.id, NOT NULL |  |
| name | VARCHAR(100) | NOT NULL |  |
| unit | VARCHAR(20) | NOT NULL | 'g', 'ml', 'cái' |
| stock_quantity | DECIMAL(12,3) | NOT NULL DEFAULT 0 | tồn kho hiện tại |
| low_stock_threshold | DECIMAL(12,3) | NOT NULL DEFAULT 0 | ngưỡng cảnh báo |

## 10. Bảng `dish_ingredients` — định lượng

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| dish_id | BIGINT UNSIGNED | FK → dishes.id, NOT NULL |  |
| ingredient_id | BIGINT UNSIGNED | FK → ingredients.id, NOT NULL |  |
| quantity | DECIMAL(10,3) | NOT NULL | định lượng cho 1 phần |

**Unique:** `(dish_id, ingredient_id)`.

### 10.1. Nghiệp vụ báo hết món

Khi khách gọi món X, trừ kho nguyên liệu của X. Nếu sau khi trừ, bất kỳ nguyên liệu nào `stock_quantity < 0` → không cho gọi, hoặc gọi xong thì tự chuyển `dishes.status = out_of_stock`.

```sql
-- Còn gọi được bao nhiêu phần món X?
SELECT MIN(FLOOR(i.stock_quantity / di.quantity)) AS max_portions
FROM dish_ingredients di
JOIN ingredients i ON i.id = di.ingredient_id
WHERE di.dish_id = :dish_id;
```

Đây là câu truy vấn “ăn điểm”: nó cho biết món còn bán được bao nhiêu phần thực tế, thay vì chỉ bật/tắt món.

## 11. Bảng `orders` — lượt gọi món

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| session_id | BIGINT UNSIGNED | FK → dining_sessions.id, NOT NULL |  |
| order_no | SMALLINT UNSIGNED | NOT NULL | lần gọi thứ mấy trong phiên |
| status | ENUM('pending','confirmed','cancelled') | NOT NULL DEFAULT 'pending' |  |
| note | TEXT | NULL | ghi chú chung |
| created_by | BIGINT UNSIGNED | NULL, FK → staff.id | NULL = khách tự gọi |

**Unique:** `(session_id, order_no)`.

> Một phiên có thể có nhiều order (khách ăn thêm). Mỗi lần bấm “Gửi bếp” tạo 1 order mới.

## 12. Bảng `order_items` — món trong order ⭐

Đây là **đơn vị trạng thái** — bếp thao tác trên từng món, không phải trên cả order.

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| order_id | BIGINT UNSIGNED | FK → orders.id, NOT NULL |  |
| dish_id | BIGINT UNSIGNED | FK → dishes.id, NOT NULL |  |
| dish_name_snapshot | VARCHAR(150) | NOT NULL | tên món lúc gọi |
| unit_price | BIGINT UNSIGNED | NOT NULL | **snapshot giá lúc gọi** |
| quantity | SMALLINT UNSIGNED | NOT NULL DEFAULT 1 |  |
| note | VARCHAR(200) | NULL | 'không hành', 'ít cay' |
| status | ENUM('pending','cooking','ready','served','cancelled') | NOT NULL DEFAULT 'pending' |  |
| started_cooking_at | DATETIME | NULL |  |
| ready_at | DATETIME | NULL |  |
| served_at | DATETIME | NULL |  |
| cancelled_at | DATETIME | NULL |  |
| cancel_reason | VARCHAR(255) | NULL |  |
| cancelled_by | BIGINT UNSIGNED | NULL, FK → staff.id |  |

**Index:** `(status, created_at)` — KDS query hàng đợi; `(order_id)` — load order detail.

### 12.1. Vì sao snapshot giá?

Giả sử khách gọi phở bò giá 60.000đ lúc 11:00. Đến 11:30 quản lý đổi giá lên 65.000đ. Nếu bill join sang `dishes.price`, khách sẽ bị tính 65.000đ — sai và gây tranh cãi. Snapshot `unit_price = 60000` bảo toàn đúng giá lúc gọi.

### 12.2. State machine trạng thái món

```
                     ┌──────────────┐
                     │   pending    │  ← vừa gọi
                     └──────┬───────┘
                            │ bếp nhận
                     ┌──────▼───────┐
                     │   cooking    │
                     └──────┬───────┘
                            │ nấu xong
                     ┌──────▼───────┐
                     │    ready     │
                     └──────┬───────┘
                            │ bưng ra bàn
                     ┌──────▼───────┐
                     │   served     │  (trạng thái cuối)
                     └──────────────┘

  Từ pending hoặc cooking ──(hủy)──► cancelled (trạng thái cuối)
  Từ ready/served KHÔNG hủy được — phải dùng chức năng trả món
```

**Luật:**
- Chỉ chuyển tiến, không lùi. Không cho `cooking → pending`.
- `served → cancelled` bị chặn (món đã ra bàn).
- Mỗi lần chuyển phải ghi timestamp tương ứng.
- Mọi chuyển trạng thái ghi `audit_logs` (ai, khi nào).

## 13. Bảng `invoices` — hóa đơn

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| session_id | BIGINT UNSIGNED | UNIQUE, FK → dining_sessions.id | 1 phiên = 1 hóa đơn |
| invoice_no | VARCHAR(30) | UNIQUE, NOT NULL | vd 'HD-20261031-0012' |
| subtotal | BIGINT UNSIGNED | NOT NULL | tổng tiền món |
| discount_amount | BIGINT UNSIGNED | NOT NULL DEFAULT 0 |  |
| service_charge | BIGINT UNSIGNED | NOT NULL DEFAULT 0 |  |
| vat_amount | BIGINT UNSIGNED | NOT NULL DEFAULT 0 |  |
| total_amount | BIGINT UNSIGNED | NOT NULL |  |
| paid_amount | BIGINT UNSIGNED | NOT NULL DEFAULT 0 |  |
| status | ENUM('draft','issued','partially_paid','paid','cancelled') | NOT NULL DEFAULT 'draft' |  |
| issued_at | TIMESTAMP | NULL |  |
| discount_note | VARCHAR(255) | NULL | lý do giảm giá |

## 14. Bảng `invoice_items` — dòng hóa đơn

| Cột | Kiểu | Ràng buộc | Ghi chú |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |
| invoice_id | BIGINT UNSIGNED | FK → invoices.id, NOT NULL |  |
| order_item_id | BIGINT UNSIGNED | NULL, FK → order_items.id | NULL nếu là dòng phụ phí |
| item_type | ENUM('dish','discount','service_charge','vat','other') | NOT NULL |  |
| description | VARCHAR(200) | NOT NULL |  |
| quantity | DECIMAL(10,2) | NOT NULL |  |
| unit_price | BIGINT UNSIGNED | NOT NULL |  |
| amount | BIGINT UNSIGNED | NOT NULL |  |
| sort_order | SMALLINT UNSIGNED | NOT NULL DEFAULT 0 |  |

## 15. Bảng `payments` — thanh toán

| Cột | Kiểu | Ràng buộc |
| --- | --- | --- |
| id | BIGINT UNSIGNED | PK |
| invoice_id | BIGINT UNSIGNED | FK → invoices.id, NOT NULL |
| amount | BIGINT UNSIGNED | NOT NULL |
| method | ENUM('cash','bank_transfer','card','other') | NOT NULL DEFAULT 'cash' |
| paid_at | DATETIME | NOT NULL |
| reference_no | VARCHAR(100) | NULL |
| recorded_by | BIGINT UNSIGNED | FK → staff.id |

## 16. Bảng `combo_rules` — luật gợi ý món kèm ⭐

| Cột | Kiểu | Ràng buộc | Ghi chú | Ghi chú |
| --- | --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK |  |  |
| antecedent_dish_id | BIGINT UNSIGNED | FK → dishes.id, NOT NULL | món khách đã chọn |  |
| consequent_dish_id | BIGINT UNSIGNED | FK → dishes.id, NOT NULL | món gợi ý |  |
| support | DECIMAL(6,4) | NOT NULL | tỉ lệ đơn có cả A và B |  |
| confidence | DECIMAL(6,4) | NOT NULL | P(B | A) |
| lift | DECIMAL(8,4) | NOT NULL | confidence / support(B) |  |
| order_count | INT UNSIGNED | NOT NULL | số đơn có cả A và B |  |
| source | ENUM('manual','fp_growth') | NOT NULL DEFAULT 'fp_growth' |  |  |
| computed_at | TIMESTAMP | NULL | lần tính gần nhất |  |

**Unique:** `(antecedent_dish_id, consequent_dish_id)`.

**Index:** `(antecedent_dish_id, confidence DESC)` — truy vấn gợi ý.

### 16.1. Ba chỉ số đánh giá luật kết hợp

| Chỉ số | Công thức | Ý nghĩa |
| --- | --- | --- |
| **Support** | `count(A ∪ B) / total_orders` | Cặp này xuất hiện bao nhiêu phần trăm đơn |
| **Confidence** | `count(A ∪ B) / count(A)` | Nếu khách gọi A, xác suất gọi B là bao nhiêu |
| **Lift** | `confidence / support(B)` | > 1 nghĩa là mua A làm tăng xác suất mua B (có tương quan thật); = 1 là độc lập; < 1 là thay thế nhau |

**Ngưỡng lọc gợi ý:** `support >= 0.01` (1% đơn), `confidence >= 0.3`, `lift >= 1.2`. Lưu ý lift quan trọng hơn confidence — một món ai cũng gọi (confidence cao tự nhiên) thì lift ≈ 1, không phải tương quan thật.

## 17. Bảng `audit_logs` — nhật ký

| Cột | Kiểu | Ràng buộc |
| --- | --- | --- |
| id | BIGINT UNSIGNED | PK |
| staff_id | BIGINT UNSIGNED | NULL, FK → staff.id |
| action | VARCHAR(50) | NOT NULL |
| auditable_type | VARCHAR(100) | NOT NULL |
| auditable_id | BIGINT UNSIGNED | NOT NULL |
| old_values | JSON | NULL |
| new_values | JSON | NULL |
| ip_address | VARCHAR(45) | NULL |
| created_at | TIMESTAMP | NOT NULL |

## 18. Tổng hợp 16 bảng

| # | Bảng | Vai trò |
| --- | --- | --- |
| 1 | restaurants | cấu hình nhà hàng |
| 2 | staff | nhân viên + phân quyền |
| 3 | areas | khu vực |
| 4 | tables | bàn + QR token |
| 5 | dining_sessions | **phiên ăn** |
| 6 | categories | danh mục món |
| 7 | dishes | món ăn |
| 8 | ingredients | nguyên liệu + tồn kho |
| 9 | dish_ingredients | định lượng |
| 10 | orders | lượt gọi món |
| 11 | order_items | **từng món + trạng thái** |
| 12 | invoices | hóa đơn |
| 13 | invoice_items | dòng hóa đơn |
| 14 | payments | thanh toán |
| 15 | combo_rules | **luật gợi ý** |
| 16 | audit_logs | nhật ký |

## 19. Thứ tự migration

```
0001_restaurants
0002_staff
0003_areas
0004_tables
0005_dining_sessions
0006_categories
0007_dishes
0008_ingredients
0009_dish_ingredients
0010_orders
0011_order_items
0012_invoices
0013_invoice_items
0014_payments
0015_combo_rules
0016_audit_logs
```

## 20. Truy vấn then chốt

**Tìm phiên đang mở của bàn (khi khách quét QR):**

```sql
SELECT * FROM dining_sessions
WHERE table_id = :table_id
  AND status IN ('open', 'awaiting_payment')
ORDER BY opened_at DESC LIMIT 1;
```

Nếu có → khách đang ngồi, dùng phiên đó. Nếu không → tạo phiên mới.

**Hàng đợi bếp (FIFO, chỉ món chưa xong):**

```sql
SELECT oi.*, d.name, d.estimated_prep_time,
       t.code AS table_code, oi.created_at,
       TIMESTAMPDIFF(MINUTE, oi.created_at, NOW()) AS waiting_minutes
FROM order_items oi
JOIN dishes d ON d.id = oi.dish_id
JOIN orders o ON o.id = oi.order_id
JOIN dining_sessions ds ON ds.id = o.session_id
JOIN tables t ON t.id = ds.table_id
WHERE oi.status IN ('pending', 'cooking')
  AND ds.status IN ('open', 'awaiting_payment')
ORDER BY oi.created_at ASC;
```

**Gợi ý món kèm (khi khách thêm món A):**

```sql
SELECT cr.consequent_dish_id, d.name, d.price, d.image_path,
       cr.confidence, cr.lift
FROM combo_rules cr
JOIN dishes d ON d.id = cr.consequent_dish_id
WHERE cr.antecedent_dish_id = :dish_id
  AND d.status = 'available'
  AND cr.confidence >= 0.30
  AND cr.lift >= 1.20
ORDER BY cr.confidence DESC, cr.lift DESC
LIMIT 3;
```

**Doanh thu theo ngày:**

```sql
SELECT DATE(i.issued_at) AS day,
       COUNT(*) AS invoice_count,
       SUM(i.total_amount) AS revenue
FROM invoices i
WHERE i.status = 'paid'
  AND i.issued_at BETWEEN :from AND :to
GROUP BY DATE(i.issued_at)
ORDER BY day;
```

**Còn bán được bao nhiêu phần món X:**

```sql
SELECT d.id, d.name,
       MIN(FLOOR(i.stock_quantity / di.quantity)) AS max_portions
FROM dishes d
JOIN dish_ingredients di ON di.dish_id = d.id
JOIN ingredients i ON i.id = di.ingredient_id
WHERE d.id = :dish_id
GROUP BY d.id, d.name;
```
