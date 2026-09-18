<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'job_title',
    'location',
    'company',
    'business_size',
    'industry',
    'business_need',
    'bio',
    'avatar',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * User's saved software.
     */
    public function savedSoftware(): HasMany
    {
        return $this->hasMany(SavedSoftware::class);
    }

    /**
     * User's software comparisons.
     */
    public function comparisons(): HasMany
    {
        return $this->hasMany(Comparison::class);
    }

    /**
     * User's implementation requests.
     */
    public function implementationRequests(): HasMany
    {
        return $this->hasMany(ImplementationRequest::class);
    }

    /**
     * User's reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}