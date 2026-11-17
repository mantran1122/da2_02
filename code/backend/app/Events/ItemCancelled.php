<?php

namespace App\Events;

use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OrderItem $item) {}

    public function broadcastOn(): array
    {
        $token = $this->item->session?->session_token;
        return [
            new Channel('table-session.' . $token),
            new Channel('kitchen')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ItemCancelled';
    }
}
