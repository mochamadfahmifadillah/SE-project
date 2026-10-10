<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Software extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'rating',
        'price',
        'views',
        'fit',
        'tag',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'views' => 'integer',
        'is_active' => 'boolean',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integrations::class, 'software_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            SoftwareFeature::class,
            'software_feature_map',
            'software_id',
            'software_feature_id'
        )->withTimestamps();
    }
}