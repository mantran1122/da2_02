<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Events\ItemStatusChanged;
use App\Events\ItemCancelled;
use Exception;

class ItemStateMachine
{
    /**
     * Bản đồ các bước chuyển trạng thái hợp lệ (chỉ cho phép đi xuôi 1 chiều)
     */
    protected array $allowedTransitions = [
        'pending' => ['cooking', 'cancelled'],
        'cooking' => ['ready', 'cancelled'],
        'ready'   => ['served'],
        'served'  => [],
        'cancelled' => [],
    ];

    /**
     * Chuyển trạng thái món và ghi nhận timestamp
     */
    public function transition(OrderItem $item, string $nextStatus, ?string $reason = null): OrderItem
    {
        $currentStatus = $item->status;

        if (! isset($this->allowedTransitions[$currentStatus]) || 
            ! in_array($nextStatus, $this->allowedTransitions[$currentStatus])) {
            throw new Exception("Không thể chuyển trạng thái từ '{$currentStatus}' sang '{$nextStatus}'.");
        }

        $now = now();
        $updates = ['status' => $nextStatus];

        match ($nextStatus) {
            'cooking' => $updates['cooking_started_at'] = $now,
            'ready'   => $updates['ready_at'] = $now,
            'served'  => $updates['served_at'] = $now,
            'cancelled' => [
                $updates['cancelled_at'] = $now,
                $updates['cancel_reason'] = $reason ?? 'Bếp hủy',
            ],
            default => null,
        };

        if (is_array($updates['cancelled_at'] ?? null)) {
            $updates['cancel_reason'] = $reason ?? 'Hủy món';
            $updates['cancelled_at'] = $now;
        }

        $item->update($updates);

        if ($nextStatus === 'cancelled') {
            broadcast(new ItemCancelled($item))->toOthers();
        } else {
            broadcast(new ItemStatusChanged($item))->toOthers();
        }

        return $item;
    }
}
