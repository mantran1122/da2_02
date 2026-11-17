<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'vat_percent',
        'service_charge_percent',
        'open_hours',
    ];

    protected $casts = [
        'vat_percent' => 'decimal:2',
        'service_charge_percent' => 'decimal:2',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
