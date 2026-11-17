<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'session_id',
        'rating_food',
        'rating_service',
        'rating_overall',
        'comment',
    ];

    protected $casts = [
        'rating_food' => 'integer',
        'rating_service' => 'integer',
        'rating_overall' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiningSession::class, 'session_id');
    }
}
