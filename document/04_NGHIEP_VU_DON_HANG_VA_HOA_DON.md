# 04 — Nghiệp vụ đơn hàng và hóa đơn

Phần này là **xương sống nghiệp vụ**. Toàn bộ công thức nằm trong `app/Services/`, không nằm trong Controller.

## 1. Vòng đời phiên ăn

```
        ┌──────────┐
        │  (chưa   │
        │  có phiên)│
        └────┬─────┘
             │ khách quét QR
        ┌────▼─────┐
        │   open   │◄──────────┐
        └────┬─────┘           │ khách gọi thêm
             │                 │
             │ khách bấm        │
             │ "Yêu cầu         │
             │  thanh toán"     │
        ┌────▼──────────┐      │
        │awaiting_payment├──────┘
        └────┬──────────┘
             │ thu ngân thu đủ tiền
        ┌────▼─────┐
        │   paid   │
        └────┬─────┘
             │ đóng phiên, bàn chuyển 'cleaning'
        ┌────▼─────┐
        │  closed  │
        └──────────┘

  Nhánh phụ: open → cancelled (hủy phiên khi khách bỏ đi không gọi gì)
```

**Luật:**

| Từ | Đến | Điều kiện |
| --- | --- | --- |
| (không có) | open | Bàn `vacant` hoặc đã có phiên `open` (dùng lại) |
| open | awaiting_payment | Có ít nhất 1 order_item `served` hoặc khách tự bấm |
| awaiting_payment | open | Khách gọi thêm món |
| awaiting_payment | paid | `paid_amount >= total_amount` |
| paid | closed | Tự động sau khi in bill, hoặc thu ngân bấm đóng |
| open | cancelled | Không có order nào, hoặc admin hủy có lý do |

> **Bẫy cần chặn:** không cho đóng phiên khi còn `order_items` ở trạng thái `pending`/`cooking`. Hệ thống báo: “Còn 2 món chưa nấu xong, không thể thanh toán.”

## 2. Tạo order

```php
// app/Services/OrderService.php

public function create(DiningSession $session, array $items, ?int $staffId = null): Order
{
    if ($session->status !== 'open') {
        throw new DomainException('Phiên đã đóng hoặc đang chờ thanh toán');
    }

    return DB::transaction(function () use ($session, $items, $staffId) {
        $orderNo = $session->orders()->max('order_no') + 1;

        $order = $session->orders()->create([
            'order_no'   => $orderNo,
            'status'     => 'pending',
            'created_by' => $staffId,
        ]);

        foreach ($items as $row) {
            $dish = Dish::lockForUpdate()->findOrFail($row['dish_id']);

            if ($dish->status !== 'available') {
                throw new DomainException("Món {$dish->name} đã hết");
            }

            // Kiểm tra còn đủ nguyên liệu không
            $maxPortions = $this->stock->maxPortions($dish->id);
            if ($maxPortions < $row['quantity']) {
                throw new DomainException(
                    "Món {$dish->name} chỉ còn {$maxPortions} phần"
                );
            }

            $order->items()->create([
                'dish_id'            => $dish->id,
                'dish_name_snapshot' => $dish->name,
                'unit_price'         => $dish->price,  // snapshot
                'quantity'           => $row['quantity'],
                'note'               => $row['note'] ?? null,
                'status'             => 'pending',
            ]);
        }

        // Trừ kho nguyên liệu
        $this->stock->deduct($order);

        return $order->load('items');
    });
}
```

**Ba điểm quan trọng:**

1. `lockForUpdate()` — nếu 2 khách cùng gọi món cuối cùng, chỉ 1 người thành công.
2. Snapshot tên + giá — thực đơn đổi không ảnh hưởng đơn cũ.
3. Trừ kho **sau** khi tạo order — nếu trừ kho lỗi, transaction rollback toàn bộ.

## 3. Trừ kho và phát hiện hết món

```php
// app/Services/StockService.php

public function deduct(Order $order): void
{
    foreach ($order->items as $item) {
        foreach ($item->dish->ingredients as $di) {
            Ingredient::where('id', $di->ingredient_id)
                ->decrement('stock_quantity', $di->quantity * $item->quantity);
        }
    }

    // Sau khi trừ, kiểm tra món nào đã hết
    $this->markOutOfStock($order);
}

public function markOutOfStock(Order $order): void
{
    $dishIds = $order->items->pluck('dish_id')->unique();

    foreach ($dishIds as $dishId) {
        if ($this->maxPortions($dishId) <= 0) {
            Dish::where('id', $dishId)->update(['status' => 'out_of_stock']);
            DishOutOfStock::dispatch($dishId);
        }
    }
}
```

Khi `DishOutOfStock` phát, WebSocket đẩy về **channel admin** và **channel kitchen** để báo. Channel khách không cần — khách đang xem thực đơn sẽ thấy món đó mờ đi ở lần load tiếp theo (hoặc thêm sau).

## 4. Chuyển trạng thái món

```php
// app/Services/ItemStateMachine.php

private const TRANSITIONS = [
    'pending'  => ['cooking', 'cancelled'],
    'cooking'  => ['ready', 'cancelled'],
    'ready'    => ['served'],
    'served'   => [],
    'cancelled'=> [],
];

public function transition(OrderItem $item, string $to, ?int $staffId = null, ?string $reason = null): void
{
    $allowed = self::TRANSITIONS[$item->status] ?? [];
    if (!in_array($to, $allowed, true)) {
        throw new DomainException(
            "Không thể chuyển từ {$item->status} sang {$to}"
        );
    }

    if ($to === 'cancelled' && !$reason) {
        throw new DomainException('Phải có lý do hủy món');
    }

    DB::transaction(function () use ($item, $to, $staffId, $reason) {
        $item->status = $to;
        match ($to) {
            'cooking'   => $item->started_cooking_at = now(),
            'ready'     => $item->ready_at = now(),
            'served'    => $item->served_at = now(),
            'cancelled' => tap($item, function () use ($staffId, $reason) {
                $this->cancelled_at   = now();
                $this->cancelled_by   = $staffId;
                $this->cancel_reason  = $reason;
            }),
            default => null,
        };
        $item->save();

        event(new OrderItemStatusChanged($item));
    });
}
```

## 5. Tính hóa đơn

### 5.1. Công thức

```
subtotal   = Σ (order_items.unit_price × quantity)   [chỉ tính status ≠ cancelled]
discount   = giảm giá theo % hoặc số tiền cố định
after_disc = subtotal - discount
service    = after_disc × service_charge_percent / 100
vat_base   = after_disc + service
vat        = vat_base × vat_percent / 100
total      = vat_base + vat
```

### 5.2. Code

```php
// app/Services/InvoiceService.php

public function buildForSession(DiningSession $session): array
{
    $items = OrderItem::query()
        ->whereHas('order', fn ($q) => $q->where('session_id', $session->id))
        ->where('status', '!=', 'cancelled')
        ->get();

    $subtotal = $items->sum(fn ($i) => $i->unit_price * $i->quantity);
    $discount = (int) ($this->discount ?? 0);
    $afterDiscount = max(0, $subtotal - $discount);

    $restaurant = $session->table->restaurant;
    $serviceCharge = (int) round(
        $afterDiscount * $restaurant->service_charge_percent / 100
    );
    $vatBase = $afterDiscount + $serviceCharge;
    $vat = (int) round($vatBase * $restaurant->vat_percent / 100);
    $total = $vatBase + $vat;

    return compact(
        'items', 'subtotal', 'discount', 'afterDiscount',
        'serviceCharge', 'vatBase', 'vat', 'total'
    );
}
```

### 5.3. Ví dụ kiểm chứng

Khách gọi: 2 phở bò (60.000) + 1 trà đá (5.000) + 1 chè (20.000) = **145.000**

| Dòng | Giá trị |
| --- | --- |
| subtotal | 145.000 |
| discount (giảm 10%) | 14.500 |
| after_discount | 130.500 |
| service_charge (5%) | 6.525 |
| vat_base | 137.025 |
| vat (8%) | 10.962 |
| **total** | **147.987** |

> Con số này dùng làm test vàng cho unit test.

## 6. Quy tắc làm tròn

| Bước | Quy tắc |
| --- | --- |
| Từng dòng món | Không làm tròn (số nguyên × số nguyên) |
| Discount % | `round(subtotal × pct / 100)` — nửa lên |
| Service charge | `round(after_discount × pct / 100)` |
| VAT | `round(vat_base × pct / 100)` |
| **Tổng** | Cộng các số **đã làm tròn** — không làm tròn lại |

**Quy tắc vàng:** `Σ invoice_items.amount = total_amount`. Test khẳng định điều này.

## 7. Thanh toán

```php
DB::transaction(function () use ($invoice, $amount, $method, $staffId) {
    $remaining = $invoice->total_amount - $invoice->paid_amount;
    if ($amount > $remaining) {
        throw new DomainException('Số tiền vượt quá công nợ còn lại');
    }

    $invoice->payments()->create([
        'amount'      => $amount,
        'method'      => $method,
        'paid_at'     => now(),
        'recorded_by' => $staffId,
    ]);

    $invoice->increment('paid_amount', $amount);
    $invoice->refresh();

    if ($invoice->paid_amount >= $invoice->total_amount) {
        $invoice->update(['status' => 'paid']);
        $invoice->session->update(['status' => 'paid']);
        $invoice->session->table->update(['status' => 'cleaning']);
    } else {
        $invoice->update(['status' => 'partially_paid']);
    }
});
```

## 8. Xử lý hủy món

Khi hủy món, phải quyết định: **có hoàn kho nguyên liệu không?**

| Trạng thái món khi hủy | Hoàn kho? | Lý do |
| --- | --- | --- |
| `pending` | **Có** | Chưa động vào nguyên liệu |
| `cooking` | **Không** | Đã bắt đầu nấu, nguyên liệu mất rồi |
| `ready` | **Không** | Đã nấu xong |

```php
if ($item->status_before === 'pending') {
    $this->stock->restore($item);
}
```

Đây là chi tiết nhỏ nhưng hội đồng hay hỏi: “hủy món thì nguyên liệu tính sao?”.

## 9. Chuyển bàn giữa phiên

Khách đang ăn ở bàn 12, muốn chuyển ra bàn 15 (rộng hơn).

```php
public function moveTable(DiningSession $session, Table $newTable): void
{
    DB::transaction(function () use ($session, $newTable) {
        if ($newTable->status !== 'vacant') {
            throw new DomainException('Bàn đích đang có khách');
        }
        if ($session->invoices()->where('status', 'paid')->exists()) {
            throw new DomainException('Phiên đã thanh toán, không chuyển được');
        }

        $oldTable = $session->table;
        $session->update(['table_id' => $newTable->id]);
        $oldTable->update(['status' => 'cleaning']);
        $newTable->update(['status' => 'occupied']);
    });
}
```

**Điểm quan trọng:** `dining_sessions.table_id` đổi, nhưng **token không đổi** — khách đang mở điện thoại không bị mất phiên.

## 10. Gộp bàn

Hai nhóm khách ngồi 2 bàn, sau đó muốn gộp thành 1 hóa đơn.

**Cách làm (đơn giản cho MVP):**
1. Chọn phiên chính (bàn A).
2. Chuyển toàn bộ `order_items` của phiên phụ (bàn B) sang một order mới trong phiên chính.
3. Đóng phiên phụ (`cancelled`, lý do “gộp bàn”).
4. Bàn B → `cleaning`.

## 11. Bảng test case nghiệp vụ

| # | Input | Kỳ vọng |
| --- | --- | --- |
| T01 | Khách quét QR bàn trống | Tạo phiên mới, status=open |
| T02 | Khách quét QR bàn đang có khách | Dùng lại phiên đang mở, không tạo mới |
| T03 | Khách khác quét QR sau khi phiên cũ đóng | Tạo phiên mới, **không thấy order cũ** |
| T04 | Gọi 3 món còn hàng | Order tạo, 3 items pending, kho trừ đúng |
| T05 | Gọi món đã `out_of_stock` | 422, không tạo order |
| T06 | Gọi 5 phần nhưng kho chỉ đủ 3 | 422, báo “chỉ còn 3 phần” |
| T07 | Gọi thêm 2 lần trong cùng phiên | Có 3 order, order_no = 1,2,3 |
| T08 | Bếp chuyển pending→cooking | OK, ghi started_cooking_at |
| T09 | Bếp chuyển pending→served (nhảy cóc) | 422 |
| T10 | Bếp chuyển served→cancelled | 422 |
| T11 | Hủy món pending | cancelled, hoàn kho |
| T12 | Hủy món cooking | cancelled, **không** hoàn kho |
| T13 | Hủy món không có lý do | 422 |
| T14 | Tính bill 145.000, giảm 10%, phí 5%, VAT 8% | 147.987 |
| T15 | Σ invoice_items = total | Bằng nhau |
| T16 | Thanh toán đúng tiền | status=paid, bàn → cleaning |
| T17 | Thanh toán một phần | partially_paid |
| T18 | Thanh toán vượt | 422 |
| T19 | Đóng phiên khi còn món cooking | 409, liệt kê món |
| T20 | Chuyển bàn khi bàn đích có khách | 422 |
| T21 | Chuyển bàn thành công | table_id đổi, token giữ nguyên |
| T22 | Gộp bàn B vào A | B cancelled, items sang A |
| T23 | Đổi giá món sau khi đã gọi | Bill cũ không đổi (snapshot) |
| T24 | Món hết nguyên liệu sau khi trừ kho | Tự động out_of_stock, broadcast |
| T25 | Khách spam 2 order trong 5 giây | Request thứ 2 bị rate limit 429 |
| T26 | Bếp subscribe channel kitchen không có token | 403 |
| T27 | Khách dùng token phiên khác để xem order | 403 |
| T28 | Admin hủy phiên chưa có order | cancelled, bàn vacant |
| T29 | Xóa món đã từng được gọi | Chặn, yêu cầu chuyển `hidden` thay vì xóa |
| T30 | Món bị ẩn (hidden) không hiện trên menu khách | Không có trong response menu |
