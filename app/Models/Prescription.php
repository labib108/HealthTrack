<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User prescription: dosage, schedule, start/end dates, linked to condition.
 */
class Prescription extends Model
{
    protected $fillable = [
        'user_id',
        'drug_id',
        'user_condition_id',
        'dosage',
        'schedule',
        'rule_id',
        'started_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function userCondition(): BelongsTo
    {
        return $this->belongsTo(UserCondition::class, 'user_condition_id');
    }

    public function administrationLogs(): HasMany
    {
        return $this->hasMany(AdministrationLog::class);
    }
}
