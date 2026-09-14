<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImplementationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'software_id',
        'message',
        'status',
        'assigned_to',
        'qualified_at',
        'proposal_sent_at',
        'closed_at',
        'lost_reason',
    ];

    protected $casts = [
        'qualified_at' => 'datetime',
        'proposal_sent_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Software
    |--------------------------------------------------------------------------
    */

    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Assigned Admin
    |--------------------------------------------------------------------------
    */

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}