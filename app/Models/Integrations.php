<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integrations extends Model
{
    protected $table = 'integrations';

    protected $fillable = [
        'software_id',
        'name',
        'description',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Software that provides this integration.
     */
    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }
}