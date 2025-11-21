<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'type',
        'event_date',
        'affected_resident_id',
        'destination',
        'metadata',
        'details',
        'processed_by',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'metadata' => 'array',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function affectedResident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'affected_resident_id');
    }
}
