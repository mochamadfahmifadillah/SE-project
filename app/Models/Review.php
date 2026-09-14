<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'software_id',
        'rating',
        'content',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * User who submitted the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Software being reviewed.
     */
    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }
}