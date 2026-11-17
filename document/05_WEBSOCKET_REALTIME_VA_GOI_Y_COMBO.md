# 05 — WebSocket realtime và gợi ý combo

Đây là phần **tạo khác biệt** của đề tài. Hai nửa: (1) đồng bộ trạng thái món tức thời, (2) gợi ý món kèm dựa trên lịch sử order.

## PHẦN A — WebSocket realtime

## 1. Vì sao cần WebSocket, không dùng polling

| Tiêu chí | Polling 2 giây | WebSocket (Reverb) |
| --- | --- | --- |
| Độ trễ | Trung bình 1 giây, tệ nhất 2 giây | < 100 ms |
| Số request | 30 req/phút/thiết bị — với 5 tablet bếp = 150 req/phút | 1 kết nối/thết bị |
| Tải server | Cao, lãng phí khi không có gì mới | Thấp |
| Trải nghiệm | Món mới hiện chậm, bếp tưởng mất order | Hiện ngay |
| Điểm đồ án | Không thể gọi là “realtime” | Đúng yêu cầu đề tài |

## 2. Cài đặt Reverb

```bash
composer require laravel/reverb
php artisan reverb:install
```

Lệnh install tự động: publish config, thêm biến `.env`, cấu hình `BROADCAST_CONNECTION=reverb`.

**config/broadcasting.php** — thêm connection:

```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'useTLS' => env('REVERB_SCHEME') === 'https',
    ],
],
```

Chạy server WebSocket:

```bash
php artisan reverb:start --debug
```

`--debug` in ra mọi sự kiện — rất hữu ích khi debug và **rất ăn điểm khi demo** (hội đồng thấy sự kiện chạy qua).

## 3. Định nghĩa sự kiện broadcast

```php
// app/Events/OrderCreated.php
class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        $restaurantId = $this->order->session->table->restaurant_id;

        return [
            new PrivateChannel("kitchen.{$restaurantId}"),
            new PrivateChannel("session.{$this->order->session->token}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'     => $this->order->id,
            'table_code'   => $this->order->session->table->code,
            'order_no'     => $this->order->order_no,
            'created_at'   => $this->order->created_at->toIso8601String(),
            'items'        => $this->order->items->map(fn ($i) => [
                'id'       => $i->id,
                'name'     => $i->dish_name_snapshot,
                'quantity' => $i->quantity,
                'note'     => $i->note,
            ])->all(),
        ];
    }
}

// app/Events/OrderItemStatusChanged.php
class OrderItemStatusChanged implements ShouldBroadcast
{
    public function __construct(public OrderItem $item) {}

    public function broadcastOn(): array
    {
        $session = $this->item->order->session;
        return [
            new PrivateChannel("session.{$session->token}"),
            new PrivateChannel("kitchen.{$session->table->restaurant_id}"),
        ];
    }

    public function broadcastAs(): string { return 'item.status'; }

    public function broadcastWith(): array
    {
        return [
            'item_id'    => $this->item->id,
            'dish_name'  => $this->item->dish_name_snapshot,
            'status'     => $this->item->status,
            'updated_at' => $this->item->updated_at->toIso8601String(),
        ];
    }
}
```

### 3.1. Bảng 6 sự kiện

| Sự kiện | Phát khi | Channel |
| --- | --- | --- |
| `order.created` | Khách gửi món | kitchen + session |
| `item.status` | Bếp đổi trạng thái món | kitchen + session |
| `dish.out_of_stock` | Hết nguyên liệu | kitchen + admin |
| `session.opened` | Khách quét QR mở phiên | table (staff) |
| `session.closed` | Thanh toán xong | table (staff) |
| `invoice.requested` | Khách yêu cầu thanh toán | table (staff) + cashier |

## 4. Client — khởi tạo Echo

```ts
// frontend/lib/echo.ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export function createEcho(token?: string) {
  window.Pusher = Pusher;

  return new Echo({
    broadcaster: 'reverb',
    key: process.env.NEXT_PUBLIC_REVERB_KEY,
    wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
    wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT),
    wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT),
    forceTLS: process.env.NEXT_PUBLIC_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
    auth: {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    },
  });
}
```

### 4.1. Bếp lắng nghe order mới

```ts
// frontend/app/kitchen/page.tsx (client component)
'use client';
import { useEffect, useState } from 'react';
import { createEcho } from '@/lib/echo';

export default function KitchenPage() {
  const [orders, setOrders] = useState<Order[]>([]);

  useEffect(() => {
    const echo = createEcho(staffToken);

    echo.private(`kitchen.${restaurantId}`)
      .listen('.order.created', (e) => {
        setOrders((prev) => [e, ...prev]);
        playSound(); // tiếng 'ting' báo có order mới
      })
      .listen('.item.status', (e) => {
        setOrders((prev) => prev.map((o) => ({
          ...o,
          items: o.items.map((i) =>
            i.id === e.item_id ? { ...i, status: e.status } : i
          ),
        })));
      })
      .listen('.dish.out_of_stock', () => refreshMenu());

    return () => echo.disconnect();
  }, []);

  return <QueueBoard orders={orders} />;
}
```

### 4.2. Khách theo dõi trạng thái món

```ts
// frontend/app/s/[tableToken]/orders/page.tsx
useEffect(() => {
  const echo = createEcho(sessionToken);
  echo.private(`session.${sessionToken}`)
    .listen('.item.status', (e) => {
      setItems((prev) =>
        prev.map((i) => i.id === e.item_id ? { ...i, status: e.status } : i)
      );
    });
  return () => echo.disconnect();
}, [sessionToken]);
```

## 5. Xử lý mất kết nối (bắt buộc — hội đồng hay hỏi)

WebSocket **có thể đứt**. Đây là cách xử lý:

```ts
const echo = createEcho(token);

// Reverb tự reconnect, nhưng sự kiện xảy ra lúc mất mạng sẽ mất.
// Bù lại bằng refetch khi kết nối lại:
echo.connector.pusher.connection.bind('connected', () => {
  refetchData();   // lấy lại toàn bộ từ DB
});

echo.connector.pusher.connection.bind('disconnected', () => {
  showBanner('Mất kết nối, đang thử lại…');
});
```

**Nguyên tắc:** WebSocket chỉ là kênh thông báo. DB luôn đúng. Khi reconnect, refetch là đủ. Trình bày rõ điều này trong báo cáo — nó chứng minh em hiểu giới hạn của realtime.

## 6. Đo độ trễ (dùng cho file 08)

```php
// Gắn timestamp vào payload
'broadcast_at' => now()->getTimestampMs(),
```

```ts
// Client đo
.listen('.order.created', (e) => {
  const latency = Date.now() - e.broadcast_at;
  metrics.push(latency);
})
```

Chạy 100 lần, tính P50 / P95. Mục tiêu: P95 < 500 ms trên mạng LAN.

## PHẦN B — Gợi ý món kèm (combo)

## 7. Hai phương án

| Phương án | Cách làm | Ưu | Nhược |
| --- | --- | --- | --- |
| **Combo cấu hình tay** | Admin chọn cặp A → B | Đơn giản, kiểm soát được, chạy ngay | Không khai thác dữ liệu, không “thông minh” |
| **FP-Growth** (chọn) | Khai phá từ lịch sử order | Đúng yêu cầu đề tài, có cơ sở dữ liệu, ăn điểm | Cần đủ lịch sử, phải làm job |

**Chiến lược đúng cho đồ án:** làm **cả hai**, dùng combo tay làm baseline, FP-Growth làm cải tiến, rồi **so sánh** — đây là cấu trúc thực nghiệm chuẩn.

## 8. Thuật toán FP-Growth

### 8.1. Vì sao FP-Growth, không phải Apriori

| Tiêu chí | Apriori | FP-Growth |
| --- | --- | --- |
| Cách sinh tập phổ biến | Sinh ứng viên rồi kiểm tra — nhiều lượt quét DB | Nén vào FP-Tree, 2 lượt quét |
| Độ phức tạp | O(2^n) ứng viên | Nhanh hơn nhiều với dữ liệu lớn |
| Bộ nhớ | Thấp | Cao hơn (giữ cây) |
| Với quán 30 bàn | Đủ dùng | **Thừa nhưng đúng chuẩn** |

Với dữ liệu nhỏ cả hai đều chạy được, nhưng FP-Growth là lựa chọn “chuẩn” trong tài liệu và ăn điểm hơn.

### 8.2. Chuẩn bị dữ liệu giao dịch

Mỗi **phiên ăn** là một giao dịch, chứa tập `dish_id` đã gọi (bỏ món cancelled).

```sql
SELECT ds.id AS session_id,
       GROUP_CONCAT(DISTINCT oi.dish_id ORDER BY oi.dish_id) AS dish_ids
FROM dining_sessions ds
JOIN orders o ON o.session_id = ds.id
JOIN order_items oi ON oi.order_id = o.id
WHERE oi.status != 'cancelled'
  AND ds.closed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY ds.id
HAVING COUNT(DISTINCT oi.dish_id) >= 2;  -- cần ít nhất 2 món mới có cặp
```

> **Quyết định quan trọng:** giao dịch là **phiên** hay **từng order**? Chọn **phiên** — vì khách gọi thêm 3 lần trong 1 bữa vẫn là một lần ăn, các món vẫn “được gọi chung”. Nếu chọn order, các cặp giữa lần gọi 1 và lần gọi 2 sẽ bị bỏ sót.

### 8.3. Code FP-Growth

```python
# analysis/mine_rules.py
import pandas as pd
from mlxtend.frequent_patterns import fpgrowth, association_rules
from mlxtend.preprocessing import TransactionEncoder
import pymysql, json, os

def load_transactions(conn):
    q = """
        SELECT ds.id, GROUP_CONCAT(DISTINCT oi.dish_id) AS dish_ids
        FROM dining_sessions ds
        JOIN orders o ON o.session_id = ds.id
        JOIN order_items oi ON oi.order_id = o.id
        WHERE oi.status != 'cancelled'
          AND ds.closed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY ds.id
        HAVING COUNT(DISTINCT oi.dish_id) >= 2
    """
    with conn.cursor() as cur:
        cur.execute(q)
        return [[int(x) for x in row[1].split(',')] for row in cur.fetchall()]

def mine(transactions, min_support=0.01, min_confidence=0.3, min_lift=1.2):
    te = TransactionEncoder()
    arr = te.fit(transactions).transform(transactions)
    df = pd.DataFrame(arr, columns=te.columns_)

    frequent = fpgrowth(df, min_support=min_support, use_colnames=True)
    rules = association_rules(frequent, metric='confidence',
                              min_threshold=min_confidence)

    # Chỉ lấy luật 1 tiền đề → 1 kết luận (dễ hiển thị cho khách)
    rules = rules[
        (rules['antecedents'].apply(len) == 1) &
        (rules['consequents'].apply(len) == 1) &
        (rules['lift'] >= min_lift)
    ]

    return rules

def save(conn, rules):
    with conn.cursor() as cur:
        cur.execute('TRUNCATE combo_rules')
        for _, r in rules.iterrows():
            cur.execute("""
                INSERT INTO combo_rules
                (antecedent_dish_id, consequent_dish_id, support,
                 confidence, lift, order_count, source, computed_at)
                VALUES (%s, %s, %s, %s, %s, %s, 'fp_growth', NOW())
            """, (
                int(list(r['antecedents'])[0]),
                int(list(r['consequents'])[0]),
                float(r['support']),
                float(r['confidence']),
                float(r['lift']),
                int(r['support'] * len(rules)),
            ))
    conn.commit()

if __name__ == '__main__':
    conn = pymysql.connect(host='127.0.0.1', user='root',
                           database='qr_order', charset='utf8mb4')
    txns = load_transactions(conn)
    print(f'{len(txns)} giao dịch')
    rules = mine(txns)
    print(f'{len(rules)} luật')
    save(conn, rules)
```

Chạy: `python analysis/mine_rules.py` — hoặc gắn vào Laravel job để chạy đêm.

## 9. Gọi gợi ý trong API

```php
// app/Services/ComboRecommendationService.php

public function forDish(int $dishId, int $limit = 3): Collection
{
    return ComboRule::query()
        ->where('antecedent_dish_id', $dishId)
        ->whereHas('consequent', fn ($q) => $q->where('status', 'available'))
        ->where('confidence', '>=', 0.30)
        ->where('lift', '>=', 1.20)
        ->orderByDesc('confidence')
        ->orderByDesc('lift')
        ->limit($limit)
        ->with('consequent')
        ->get()
        ->pluck('consequent');
}
```

Endpoint: `GET /api/menu/dishes/{id}/suggestions` → trả 3 món gợi ý kèm `confidence` để hiển thị “92% khách gọi món này cũng gọi…”.

## 10. Cách đánh giá gợi ý (bắt buộc có trong báo cáo)

### 10.1. Precision@3

Lấy 20% phiên gần nhất làm **test set**. Với mỗi phiên, lấy món đầu tiên làm input, hỏi hệ thống gợi ý 3 món, xem có món nào thực sự nằm trong phiên đó không.

```python
hits = 0
for session in test_sessions:
    first = session[0]
    actual = set(session[1:])
    predicted = recommend(first, k=3)
    if predicted & actual:
        hits += 1

precision_at_3 = hits / len(test_sessions)
```

### 10.2. Bảng so sánh để đưa vào báo cáo

| Phương pháp | Precision@3 | Ghi chú |
| --- | --- | --- |
| Ngẫu nhiên (baseline) | __% | Chọn 3 món bất kỳ |
| Món bán chạy nhất (baseline) | __% | Top 3 món doanh thu cao |
| Combo cấu hình tay | __% | Do admin chọn |
| **FP-Growth** | __% | Đề xuất của đồ án |

> **Bắt buộc có baseline ngẫu nhiên.** Nếu FP-Growth đạt 40% mà ngẫu nhiên cũng 35% thì gợi ý gần như vô dụng — phải trung thực báo cáo. Đây là điểm hội đồng rất hay bắt lỗi.

### 10.3. Nếu không đủ dữ liệu

Với quán mới, lịch sử order ít → FP-Growth sinh rất ít luật. Xử lý:

1. **Seed dữ liệu mẫu** cho demo (ghi rõ là dữ liệu mô phỏng).
2. **Fallback** về top món bán chạy khi không có luật.
3. Ghi rõ trong hạn chế: “cần tối thiểu ~200 phiên để luật có ý nghĩa thống kê”.

## 11. Bảng test case cho phần này

| # | Kịch bản | Kỳ vọng |
| --- | --- | --- |
| W01 | Khách gửi order, bếp đang mở KDS | Bếp thấy món trong < 1 giây |
| W02 | Bếp đổi trạng thái, khách đang xem | Khách thấy cập nhật < 1 giây |
| W03 | Tắt wifi bếp 10 giây rồi bật lại | Reconnect, refetch đủ dữ liệu |
| W04 | Reverb server chết | Client hiện banner “mất kết nối”, API vẫn hoạt động |
| W05 | Người lạ subscribe channel kitchen | 403 |
| W06 | Khách A subscribe channel phiên B | 403 |
| W07 | 5 tablet bếp cùng nhận 1 order | Cả 5 đều nhận |
| W08 | Đo P95 latency 100 lần | < 500 ms |
| C01 | Gọi món A, có luật A→B | API trả gợi ý B |
| C02 | Luật A→B nhưng B đã hết | Không gợi ý B |
| C03 | Món A chưa có luật | Trả mảng rỗng, không lỗi |
| C04 | Chạy lại job FP-Growth | Bảng combo_rules được thay mới, không nhân đôi |
| C05 | Confidence thấp (< 0.3) | Không hiện gợi ý |
| C06 | Lift < 1.2 | Không hiện gợi ý |
