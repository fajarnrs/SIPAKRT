<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Models\Rt;
use App\Models\Resident;

class RtOfficial extends Model
{
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'resident_id',
        'position',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    protected static function booted(): void
    {
        static::saved(function (self $official): void {
            $official->refreshRtLeader();
        });

        static::deleted(function (self $official): void {
            $official->refreshRtLeader();
        });
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('ended_at')
                ->orWhereDate('ended_at', '>', Carbon::today());
        });
    }

    public function endNow(): void
    {
        $this->update(['ended_at' => now()->toDateString()]);
    }

    public function isActive(): bool
    {
        if (is_null($this->ended_at)) {
            return true;
        }

        return $this->ended_at instanceof Carbon
            ? $this->ended_at->isFuture()
            : Carbon::parse($this->ended_at)->isFuture();
    }

    protected function refreshRtLeader(): void
    {
        if (! $this->relationLoaded('rt')) {
            $this->load('rt');
        }

        $rt = $this->rt;

        if (! $rt) {
            return;
        }

        $current = $rt->officials()
            ->active()
            ->orderByDesc('started_at')
            ->first();

        if ($current) {
            $current->loadMissing('resident');
            $rt->leader_resident_id = $current->resident_id;
            $rt->leader_name = $current->resident?->name;
            $rt->save();
            $rt->syncLeaderUser($current->resident);
        } else {
            $rt->leader_resident_id = null;
            $rt->leader_name = null;
            $rt->save();
            $rt->removeRtUser();
        }
    }
}
