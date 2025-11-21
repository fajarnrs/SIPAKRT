<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Models\RtOfficial;
use App\Models\User;
use App\Models\Resident;
use Illuminate\Support\Arr;

class Rt extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'leader_name',
        'leader_resident_id',
        'email',
        'notes',
    ];

    public function households(): HasMany
    {
        return $this->hasMany(Household::class);
    }

    public function residents(): HasManyThrough
    {
        return $this->hasManyThrough(Resident::class, Household::class);
    }

    public function leaderResident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'leader_resident_id');
    }

    public function currentLeaderRecord(): ?RtOfficial
    {
        if ($this->relationLoaded('officials')) {
            return $this->officials
                ->filter(fn (RtOfficial $official) => $official->isActive())
                ->sortByDesc('started_at')
                ->first();
        }

        return $this->officials()
            ->active()
            ->orderByDesc('started_at')
            ->with('resident')
            ->first();
    }

    public function currentLeaderName(): ?string
    {
        $official = $this->currentLeaderRecord();

        return $official?->resident?->name ?? $this->leader_name;
    }

    public function officials(): HasMany
    {
        return $this->hasMany(RtOfficial::class);
    }

    public function guestLogs(): HasMany
    {
        return $this->hasMany(GuestLog::class);
    }

    public function assistancePrograms(): HasMany
    {
        return $this->hasMany(AssistanceProgram::class);
    }

    public function leaderUser(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function syncLeaderUser(?Resident $resident): void
    {
        if (! $resident) {
            $this->removeRtUser();
            return;
        }

        $resident->loadMissing('household');
        $household = $resident->household;

        $username = $household?->family_card_number ?? $resident->email;

        if (! $username) {
            $this->removeRtUser();
            return;
        }

        $user = User::firstOrNew(['email' => $username]);

        $user->email = $username;
        $user->name = $household?->head_name ?? $resident->name;
        $user->role = 'rt';
        $user->rt_id = $this->id;
        $user->household_id = $household?->id ?? $resident->household_id;
        $user->is_admin = false;
        $user->is_active = true;

        if (! $user->exists || empty($user->password)) {
            $user->password = bcrypt(config('app.default_rt_password', 'password123'));
        }

        $user->save();
    }

    public function removeRtUser(): void
    {
        User::where('rt_id', $this->id)
            ->where('role', 'rt')
            ->get()
            ->each(function (User $user): void {
                $resident = Resident::where('email', $user->email)->first();

                $user->role = 'warga';
                $user->rt_id = null;
                $user->is_active = true;
                $user->household_id = $resident?->household_id ?? $user->household_id;
                $user->save();
            });
    }
}
