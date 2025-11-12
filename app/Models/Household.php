<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'family_card_number',
        'head_name',
        'head_nik',
        'head_gender',
        'head_birth_place',
        'head_birth_date',
        'head_religion',
        'head_education',
        'head_occupation',
        'head_marital_status',
        'head_nationality',
        'head_status',
        'head_email',
        'head_notes',
        'address',
        'issued_at',
        'status',
        'status_effective_date',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'head_birth_date' => 'date',
        'status_effective_date' => 'date',
    ];

    public const STATUS_ACTIVE = 'aktif';
    public const STATUS_INACTIVE = 'non-aktif';
    public const STATUS_EXPIRED = 'expired';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Non-Aktif',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(HouseholdMovement::class);
    }

    public function assistanceRecipients(): HasMany
    {
        return $this->hasMany(AssistanceRecipient::class);
    }
}
