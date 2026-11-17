<?php

namespace App\Events;

use App\Models\DiningSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DiningSession $session) {}

    public function broadcastOn(): array
    {
        return [new Channel('cashier')];
    }

    public function broadcastAs(): string
    {
        return 'PaymentRequested';
    }
}
