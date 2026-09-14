<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSoftware extends Model
{
    protected $table = 'saved_software';

    protected $fillable = [
        'user_id',
        'software_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }
}