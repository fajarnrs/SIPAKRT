<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'nik',
        'name',
        'relationship',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'education',
        'occupation',
        'marital_status',
        'email',
        'nationality',
        'status',
        'status_effective_at',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'status_effective_at' => 'date',
    ];

    public const STATUS_ACTIVE = 'aktif';
    public const STATUS_DECEASED = 'meninggal';
    public const STATUS_MOVED = 'pindah';
    public const STATUS_TEMPORARY = 'sementara';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_DECEASED => 'Meninggal',
            self::STATUS_MOVED => 'Pindah',
            self::STATUS_TEMPORARY => 'Tidak Tinggal Sementara',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
