<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'invoice_code',
        'subtotal',
        'discount_amount',
        'discount_note',
        'service_charge',
        'vat_amount',
        'total_amount',
        'status',
        'issued_by_staff_id',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'service_charge' => 'integer',
        'vat_amount' => 'integer',
        'total_amount' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiningSession::class, 'session_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'issued_by_staff_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
