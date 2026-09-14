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

    /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    */

    /**
     * Software reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    */

    /**
     * Software integrations.
     *
     * Requires a pivot table such as:
     * software_integration
     */
    public function integrations(): BelongsToMany
    {
        return $this->belongsToMany(
            Integration::class,
            'software_integration',
            'software_id',
            'integration_id'
        );
    }
}