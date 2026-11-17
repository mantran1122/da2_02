<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'session_id',
        'dish_id',
        'quantity',
        'unit_price',
        'note',
        'status',
        'cancel_reason',
        'cooking_started_at',
        'ready_at',
        'served_at',
        'cancelled_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'cooking_started_at' => 'datetime',
        'ready_at' => 'datetime',
        'served_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiningSession::class, 'session_id');
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
