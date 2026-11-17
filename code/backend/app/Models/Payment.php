<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'transaction_ref',
        'status',
        'received_by_staff_id',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by_staff_id');
    }
}
