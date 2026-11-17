<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image_url',
        'price',
        'unit',
        'estimated_prep_time',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'integer',
        'estimated_prep_time' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dishIngredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients')
            ->withPivot('quantity_needed', 'unit')
            ->withTimestamps();
    }

    public function comboRulesAsAntecedent(): HasMany
    {
        return $this->hasMany(ComboRule::class, 'antecedent_dish_id');
    }
}
