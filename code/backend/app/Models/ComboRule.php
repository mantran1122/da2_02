<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'antecedent_dish_id',
        'consequent_dish_id',
        'support',
        'confidence',
        'lift',
        'source',
        'is_active',
    ];

    protected $casts = [
        'support' => 'decimal:4',
        'confidence' => 'decimal:4',
        'lift' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function antecedentDish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'antecedent_dish_id');
    }

    public function consequentDish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'consequent_dish_id');
    }
}
