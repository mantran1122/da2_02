# 08 — Kiểm thử và đánh giá

## 1. Chiến lược test

| Loại | Công cụ | Số lượng | Mục tiêu |
| --- | --- | --- | --- |
| Unit test state machine | PHPUnit | 8+ | 100% pass, chặn mọi chuyển trạng thái sai |
| Unit test tính tiền | PHPUnit | 8+ | 100% pass, gồm case biên |
| Feature test API | PHPUnit | 20+ | Mỗi endpoint 1 happy + 1 lỗi |
| Test thủ công UI | Checklist | 25 kịch bản | Chạy trước bảo vệ |
| Đo latency WebSocket | Script | 100 lần | P50/P95 |
| Đánh giá gợi ý | Script Python | test set 20% | Precision@3 so 3 baseline |

## 2. Unit test tính tiền (test vàng)

```php
// tests/Unit/InvoiceServiceTest.php
public function test_golden_case_145k(): void
{
    $session = $this->makeSessionWithItems([
        ['unit_price' => 60000, 'quantity' => 2],  // phở bò
        ['unit_price' => 5000,  'quantity' => 1],  // trà đá
        ['unit_price' => 20000, 'quantity' => 1],  // chè
    ]);
    $this->restaurant->update([
        'vat_percent' => 8, 'service_charge_percent' => 5,
    ]);

    $result = $this->service->buildForSession($session, discount: 14500);

    $this->assertSame(145000, $result['subtotal']);
    $this->assertSame(14500,  $result['discount']);
    $this->assertSame(130500, $result['afterDiscount']);
    $this->assertSame(6525,   $result['serviceCharge']);
    $this->assertSame(137025, $result['vatBase']);
    $this->assertSame(10962,  $result['vat']);
    $this->assertSame(147987, $result['total']);
}

public function test_cancelled_items_are_excluded(): void
{
    $session = $this->makeSessionWithItems([
        ['unit_price' => 60000, 'quantity' => 2, 'status' => 'served'],
        ['unit_price' => 45000, 'quantity' => 1, 'status' => 'cancelled'],
    ]);
    $result = $this->service->buildForSession($session);
    $this->assertSame(120000, $result['subtotal']);
}

public function test_vat_applies_after_service_charge(): void
{
    // Nếu áp VAT trước service charge sẽ ra số khác — test bắt lỗi này
    $session = $this->makeSessionWithItems([
        ['unit_price' => 100000, 'quantity' => 1],
    ]);
    $this->restaurant->update([
        'vat_percent' => 10, 'service_charge_percent' => 5,
    ]);
    $result = $this->service->buildForSession($session);
    // Đúng: 100000 → service 5000 → vatBase 105000 → vat 10500 → total 115500
    // Sai (vat trước): 10000 + 5000 → 115000
    $this->assertSame(115500, $result['total']);
}
```

## 3. Unit test state machine

```php
// tests/Unit/ItemStateMachineTest.php
public function test_valid_transitions(): void
{
    $item = OrderItem::factory()->create(['status' => 'pending']);

    $this->machine->transition($item, 'cooking');
    $this->assertNotNull($item->fresh()->started_cooking_at);

    $this->machine->transition($item->fresh(), 'ready');
    $this->assertNotNull($item->fresh()->ready_at);

    $this->machine->transition($item->fresh(), 'served');
    $this->assertSame('served', $item->fresh()->status);
}

public function test_cannot_skip_to_served(): void
{
    $item = OrderItem::factory()->create(['status' => 'pending']);
    $this->expectException(DomainException::class);
    $this->machine->transition($item, 'served');
}

public function test_cannot_go_backwards(): void
{
    $item = OrderItem::factory()->create(['status' => 'cooking']);
    $this->expectException(DomainException::class);
    $this->machine->transition($item, 'pending');
}

public function test_cannot_cancel_served_item(): void
{
    $item = OrderItem::factory()->create(['status' => 'served']);
    $this->expectException(DomainException::class);
    $this->machine->transition($item, 'cancelled', reason: 'test');
}

public function test_cancel_requires_reason(): void
{
    $item = OrderItem::factory()->create(['status' => 'pending']);
    $this->expectException(DomainException::class);
    $this->machine->transition($item, 'cancelled');
}
```

## 4. Test chống gọi trùng món cuối cùng (concurrency)

Đây là test khó nhưng rất ăn điểm — chứng minh em hiểu race condition.

```php
public function test_only_one_guest_gets_last_portion(): void
{
    // Kho chỉ đủ 1 phần phở
    $this->ingredient->update(['stock_quantity' => $this->phoRecipe['quantity']]);

    $results = [];
    foreach ([$sessionA, $sessionB] as $session) {
        try {
            $this->orderService->create($session, [
                ['dish_id' => $this->pho->id, 'quantity' => 1],
            ]);
            $results[] = 'ok';
        } catch (DomainException $e) {
            $results[] = 'rejected';
        }
    }

    $this->assertSame(['ok', 'rejected'], $results);
}
```

> Test này chạy tuần tự sẽ luôn pass, nhưng code có `lockForUpdate` là thứ **đảm bảo** nó pass. Trình bày trong báo cáo: “dùng pessimistic lock để chống overbooking món”.

## 5. Bảng test case nghiệp vụ (tham chiếu file 04)

30 case T01–T30 đã liệt kê ở `04_NGHIEP_VU_DON_HANG_VA_HOA_DON.md` mục 11. Ở đây bổ sung nhóm test cho WebSocket và combo (W01–W08, C01–C06 trong `05_WEBSOCKET_REALTIME_VA_GOI_Y_COMBO.md` mục 11).

## 6. Checklist test thủ công

```markdown
## Khách (điện thoại thật)
- [ ] Quét QR bằng camera điện thoại → mở đúng bàn
- [ ] Thực đơn hiển thị ảnh, cuộn mượt
- [ ] Thêm 3 món → giỏ hiện đúng tổng tiền
- [ ] Gửi bếp → thấy món trong trang orders
- [ ] Bếp đổi trạng thái → khách thấy đổi màu không F5
- [ ] Bấm yêu cầu thanh toán → nhân viên thấy
- [ ] Xoay ngang màn hình → layout không vỡ

## Bếp (tablet)
- [ ] Order mới hiện ngay kèm tiếng
- [ ] Đồng hồ đếm phút chạy đúng
- [ ] Món quá 10 phút viền đỏ
- [ ] Bấm 'Bắt đầu nấu' → trạng thái đổi, nút đổi thành 'Xong'
- [ ] Hủy món không nhập lý do → bị chặn
- [ ] Báo hết món → món mờ trên menu khách sau refresh

## Thu ngân
- [ ] Xem bill tạm tính khớp
- [ ] Áp giảm giá 10% → tổng đổi đúng
- [ ] Thu một phần → badge 'thanh toán một phần'
- [ ] Thu đủ → phiên đóng, bàn chuyển 'dọn dẹp'
- [ ] Xuất PDF → mở được, số liệu khớp

## Kết nối
- [ ] Tắt wifi bếp 10 giây → banner vàng, bật lại → tự phục hồi
- [ ] Tắt Reverb server → client báo mất kết nối, API vẫn dùng được
- [ ] Mở 3 tab bếp cùng lúc → cả 3 nhận order
```

## 7. Đo độ trễ WebSocket (bắt buộc có số liệu)

### 7.1. Cách đo

Gắn `broadcast_at` vào payload (đã mô tả ở file 05 mục 6), client tính `Date.now() - broadcast_at`.

```ts
// frontend/lib/metrics.ts
const samples: number[] = [];

export function recordLatency(ms: number) {
  samples.push(ms);
  if (samples.length === 100) {
    const sorted = [...samples].sort((a, b) => a - b);
    const p = (q: number) => sorted[Math.floor(sorted.length * q)];
    console.log({
      p50: p(0.5), p95: p(0.95), max: sorted.at(-1),
    });
    exportCsv(samples);
  }
}
```

### 7.2. Bảng kết quả điền vào báo cáo

| Môi trường | Số mẫu | P50 | P95 | Max |
| --- | --- | --- | --- | --- |
| Localhost (cùng máy) | 100 | __ ms | __ ms | __ ms |
| LAN wifi quán (giả lập) | 100 | __ ms | __ ms | __ ms |
| 4G ngoài (nếu test được) | 100 | __ ms | __ ms | __ ms |

**Kỳ vọng:** localhost P95 < 150 ms; LAN P95 < 500 ms.

### 7.3. So sánh với polling (để chứng minh giá trị)

| Phương án | Độ trễ trung bình | Số request/phút/thiết bị |
| --- | --- | --- |
| Polling 2 giây | ~1000 ms | 30 |
| **WebSocket** | **__ ms** | **0** (chỉ 1 kết nối) |

> Bảng này là **điểm nhấn** của phần realtime. Đo thật, đừng bịa.

## 8. Đánh giá gợi ý combo

### 8.1. Chia dữ liệu

| Tập | Tỉ lệ | Dùng cho |
| --- | --- | --- |
| Train | 80% phiên cũ nhất | Chạy FP-Growth sinh luật |
| Test | 20% phiên mới nhất | Đánh giá Precision@3 |

**Quan trọng:** chia theo **thời gian**, không chia ngẫu nhiên. Chia ngẫu nhiên sẽ rò rỉ thông tin tương lai vào train — sai phương pháp luận, hội đồng có thể bắt lỗi.

### 8.2. Code đánh giá

```python
# analysis/evaluate.py
def precision_at_k(test_sessions, recommend_fn, k=3):
    hits = 0
    for session in test_sessions:
        if len(session) < 2:
            continue
        seed = session[0]
        actual = set(session[1:])
        predicted = set(recommend_fn(seed, k))
        if predicted & actual:
            hits += 1
    return hits / len(test_sessions)

# 4 phương pháp
random_rec = lambda seed, k: random.sample(all_dish_ids, k)
popular_rec = lambda seed, k: top_selling_dish_ids[:k]
manual_rec = lambda seed, k: manual_combo_map.get(seed, [])[:k]
fpgrowth_rec = lambda seed, k: rules_by_antecedent.get(seed, [])[:k]

for name, fn in [('random', random_rec), ('popular', popular_rec),
                 ('manual', manual_rec), ('fp_growth', fpgrowth_rec)]:
    print(name, precision_at_k(test_sessions, fn))
```

### 8.3. Bảng kết quả (điền số thật)

| Phương pháp | Precision@3 | Ghi chú |
| --- | --- | --- |
| Ngẫu nhiên (baseline dưới) | __% | Sàn tham chiếu |
| Top món bán chạy (baseline mạnh) | __% | Rất khó vượt — nếu vượt được thì tốt |
| Combo cấu hình tay | __% | Phụ thuộc admin |
| **FP-Growth** | __% | Đề xuất |

**Cách đọc kết quả trung thực:**

- Nếu FP-Growth **vượt** top-bán-chạy: kết luận mạnh, nêu rõ.
- Nếu FP-Growth **thua** top-bán-chạy: báo cáo trung thực, giải thích (dữ liệu ít, top-bán-chạy đã rất mạnh với quán nhỏ), đề xuất hướng cải tiến (kết hợp cả hai: nếu có luật thì dùng luật, không có thì fallback top-bán-chạy).
- Tuyệt đối không chỉnh tham số trên test set để đẹp số — đó là gian lận phương pháp.

### 8.4. Phân tích lỗi gợi ý

| Loại lỗi | Ví dụ | Nguyên nhân |
| --- | --- | --- |
| Gợi ý món cùng loại | Gọi phở → gợi ý bún | Món thay thế có lift < 1 nhưng confidence cao |
| Gợi ý món ai cũng gọi | Gợi ý trà đá | Lift ≈ 1, không phải tương quan thật |
| Không có luật | Món mới chưa ai gọi chung | Chưa đủ dữ liệu |

Bảng này chứng minh em hiểu vì sao phải lọc theo `lift` chứ không chỉ `confidence`.

## 9. Kịch bản demo trước hội đồng (7 phút)

| Phút | Nội dung | Chuẩn bị trước |
| --- | --- | --- |
| 0:00–0:30 | Bài toán: phiếu giấy, sai món, bếp không biết ưu tiên | Ảnh phiếu giấy thật |
| 0:30–1:30 | Quét QR bằng điện thoại thật → menu → thêm 3 món | Điện thoại kết nối wifi, dữ liệu sạch |
| 1:30–2:30 | Gửi bếp → **màn hình bếp hiện ngay** (cho hội đồng thấy đồng hồ đếm giây) | KDS mở sẵn trên tablet/màn hình 2 |
| 2:30–3:30 | Bếp đổi trạng thái → **điện thoại khách đổi màu realtime** | Đặt điện thoại cạnh màn hình để so sánh |
| 3:30–4:30 | Xem gợi ý combo ở chi tiết món → giải thích FP-Growth + confidence | Món đã chọn phải có luật |
| 4:30–5:30 | Thu ngân: xem bill, giảm 10%, thu tiền, xuất PDF |  |
| 5:30–6:30 | Bảng đo latency + bảng Precision@3 (slide) | Slide có số thật |
| 6:30–7:00 | Kết luận + hạn chế |  |

**Ba nguyên tắc demo:**

1. **Bật đủ 4 process trước khi vào phòng.** Kiểm tra bằng cách gửi 1 order test.
2. **Có phương án 2:** quay video demo trước; nếu Reverb chết, phát video và giải thích.
3. **Đừng để hội đồng chờ màn hình trắng.** Luôn có dữ liệu seed sẵn, không phụ thuộc mạng ngoài.

## 10. Bảng tiêu chí đánh giá cuối

| Tiêu chí | Ngưỡng đạt | Thực tế |
| --- | --- | --- |
| Unit test state machine | 100% | __ |
| Unit test tính tiền | 100% | __ |
| Feature test API | ≥ 95% | __ |
| Latency WebSocket P95 (LAN) | < 500 ms | __ |
| Precision@3 của FP-Growth | > random baseline | __ |
| Flow end-to-end không lỗi | Có | __ |
| Hiển thị tốt trên 375px | Có | __ |
| Reconnect sau mất mạng | Có | __ |
