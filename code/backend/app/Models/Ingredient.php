<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'current_stock',
        'unit',
        'minimum_threshold',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_threshold' => 'decimal:2',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function dishIngredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }
}
