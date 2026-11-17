<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DiningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'session_token',
        'status',
        'customer_count',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'customer_count' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'session_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'session_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'session_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class, 'session_id');
    }
}
