<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistance_program_id',
        'household_id',
        'resident_id',
        'received_at',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(AssistanceProgram::class, 'assistance_program_id');
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
