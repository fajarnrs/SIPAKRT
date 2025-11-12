<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'resident_id',
        'guest_name',
        'guest_id_number',
        'origin',
        'purpose',
        'visit_date',
        'arrival_time',
        'departure_time',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
