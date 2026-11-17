<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'code',
        'capacity',
        'qr_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function diningSessions(): HasMany
    {
        return $this->hasMany(DiningSession::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(DiningSession::class)->where('status', 'open');
    }
}
