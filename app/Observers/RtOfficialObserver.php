<?php

namespace App\Observers;

use App\Models\RtOfficial;
use App\Models\Resident;
use App\Models\User;

class RtOfficialObserver
{
    /**
     * Handle the RtOfficial "created" event.
     */
    public function created(RtOfficial $rtOfficial): void
    {
        $this->updateUserRole($rtOfficial);
    }

    /**
     * Handle the RtOfficial "updated" event.
     */
    public function updated(RtOfficial $rtOfficial): void
    {
        $this->updateUserRole($rtOfficial);
    }

    /**
     * Handle the RtOfficial "deleted" event.
     */
    public function deleted(RtOfficial $rtOfficial): void
    {
        // Kembalikan role ke warga jika jabatan dihapus
        $resident = Resident::find($rtOfficial->resident_id);
        if ($resident && $resident->user_id) {
            $user = User::find($resident->user_id);
            if ($user && $user->role === 'rt') {
                // Cek apakah masih punya jabatan aktif lain
                $hasActiveRole = RtOfficial::where('resident_id', $resident->id)
                    ->where('id', '!=', $rtOfficial->id)
                    ->whereNull('ended_at')
                    ->exists();
                
                if (!$hasActiveRole) {
                    $user->update([
                        'role' => 'warga',
                        'rt_id' => null,
                    ]);
                }
            }
        }
    }

    /**
     * Handle the RtOfficial "restored" event.
     */
    public function restored(RtOfficial $rtOfficial): void
    {
        $this->updateUserRole($rtOfficial);
    }

    /**
     * Handle the RtOfficial "force deleted" event.
     */
    public function forceDeleted(RtOfficial $rtOfficial): void
    {
        //
    }
    
    /**
     * Update user role based on RT Official status
     */
    protected function updateUserRole(RtOfficial $rtOfficial): void
    {
        $resident = Resident::find($rtOfficial->resident_id);
        
        if (!$resident || !$resident->user_id) {
            return;
        }
        
        $user = User::find($resident->user_id);
        
        if (!$user) {
            return;
        }
        
        // Jika jabatan masih aktif (ended_at kosong atau di masa depan)
        if ($rtOfficial->isActive()) {
            $user->update([
                'role' => 'rt',
                'rt_id' => $rtOfficial->rt_id,
            ]);
        } else {
            // Jika jabatan sudah berakhir, cek apakah masih punya jabatan aktif lain
            $hasActiveRole = RtOfficial::where('resident_id', $resident->id)
                ->where('id', '!=', $rtOfficial->id)
                ->whereNull('ended_at')
                ->exists();
            
            if (!$hasActiveRole) {
                $user->update([
                    'role' => 'warga',
                    'rt_id' => null,
                ]);
            }
        }
    }
}
