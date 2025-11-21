<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistanceProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'name',
        'category',
        'source',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(AssistanceRecipient::class);
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }
}
