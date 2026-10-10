<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoftwareFeature extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function software(): BelongsToMany
    {
        return $this->belongsToMany(
            Software::class,
            'software_feature_map',
            'software_feature_id',
            'software_id'
        )->withTimestamps();
    }
}