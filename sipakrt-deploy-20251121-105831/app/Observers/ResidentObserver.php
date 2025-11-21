<?php

namespace App\Observers;

use App\Models\Resident;
use App\Models\Household;

class ResidentObserver
{
    /**
     * Handle the Resident "created" event.
     */
    public function created(Resident $resident): void
    {
        $this->updateHouseholdStatus($resident);
    }

    /**
     * Handle the Resident "updated" event.
     */
    public function updated(Resident $resident): void
    {
        // Jika status atau marital status berubah, update status KK
        if ($resident->wasChanged('status') || $resident->wasChanged('marital_status')) {
            $this->updateHouseholdStatus($resident);
        }
    }

    /**
     * Handle the Resident "deleted" event.
     */
    public function deleted(Resident $resident): void
    {
        //
    }

    /**
     * Handle the Resident "restored" event.
     */
    public function restored(Resident $resident): void
    {
        //
    }

    /**
     * Handle the Resident "force deleted" event.
     */
    public function forceDeleted(Resident $resident): void
    {
        //
    }

    /**
     * Update status KK jika kepala keluarga meninggal atau cerai
     */
    protected function updateHouseholdStatus(Resident $resident): void
    {
        // Hanya proses jika resident adalah kepala keluarga
        if ($resident->relationship !== 'Kepala Keluarga') {
            return;
        }

        $household = $resident->household;
        
        if (!$household) {
            return;
        }

        // Jika kepala keluarga meninggal, set KK jadi non-aktif
        if ($resident->status === Resident::STATUS_DECEASED) {
            $household->update([
                'status' => Household::STATUS_INACTIVE,
                'status_effective_date' => $resident->status_effective_at ?? now(),
            ]);
            return;
        }

        // Jika kepala keluarga cerai, set KK jadi non-aktif
        if ($resident->marital_status === 'Cerai Hidup' || $resident->marital_status === 'Cerai Mati') {
            $household->update([
                'status' => Household::STATUS_INACTIVE,
                'status_effective_date' => now(),
            ]);
            return;
        }

        // Jika status kepala keluarga kembali aktif, aktifkan KK
        if ($resident->status === Resident::STATUS_ACTIVE) {
            $household->update([
                'status' => Household::STATUS_ACTIVE,
                'status_effective_date' => now(),
            ]);
        }
    }
}
