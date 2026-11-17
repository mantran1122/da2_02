<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'order_no',
        'status',
        'note',
    ];

    protected $casts = [
        'order_no' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiningSession::class, 'session_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
