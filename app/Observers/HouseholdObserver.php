<?php

namespace App\Observers;

use App\Models\Household;
use App\Models\Resident;

class HouseholdObserver
{
    /**
     * Handle the Household "created" event.
     */
    public function created(Household $household): void
    {
        $this->syncHeadResident($household);
    }

    /**
     * Handle the Household "updated" event.
     */
    public function updated(Household $household): void
    {
        // Hanya sync jika ada perubahan pada field kepala keluarga
        if ($household->wasChanged([
            'head_name', 'head_nik', 'head_gender', 'head_birth_place', 
            'head_birth_date', 'head_religion', 'head_education', 
            'head_occupation', 'head_marital_status', 'head_status'
        ])) {
            $this->syncHeadResident($household);
        }
    }

    /**
     * Handle the Household "deleted" event.
     */
    public function deleted(Household $household): void
    {
        //
    }

    /**
     * Handle the Household "restored" event.
     */
    public function restored(Household $household): void
    {
        //
    }

    /**
     * Handle the Household "force deleted" event.
     */
    public function forceDeleted(Household $household): void
    {
        //
    }

    /**
     * Sync head resident dari data household
     */
    protected function syncHeadResident(Household $household): void
    {
        // Jika tidak ada head_name, hapus kepala keluarga jika ada
        if (empty(trim($household->head_name ?? ''))) {
            Resident::where('household_id', $household->id)
                ->where('relationship', 'Kepala Keluarga')
                ->delete();
            return;
        }

        // Jika head_status bukan aktif, hapus kepala keluarga
        if (($household->head_status ?? Resident::STATUS_ACTIVE) !== Resident::STATUS_ACTIVE) {
            Resident::where('household_id', $household->id)
                ->where('relationship', 'Kepala Keluarga')
                ->delete();
            return;
        }

        // Create atau update resident kepala keluarga
        $residentData = [
            'name' => $household->head_name,
            'nik' => $household->head_nik,
            'gender' => $household->head_gender,
            'birth_place' => $household->head_birth_place,
            'birth_date' => $household->head_birth_date,
            'religion' => $household->head_religion,
            'education' => $household->head_education,
            'occupation' => $household->head_occupation,
            'marital_status' => $household->head_marital_status,
            'email' => $household->head_email,
            'nationality' => $household->head_nationality ?? 'WNI',
            'status' => $household->head_status ?? Resident::STATUS_ACTIVE,
            'notes' => $household->head_notes,
            'relationship' => 'Kepala Keluarga',
        ];

        // Cari resident kepala keluarga yang sudah ada
        $headResident = Resident::where('household_id', $household->id)
            ->where('relationship', 'Kepala Keluarga')
            ->first();

        if ($headResident) {
            // Update yang sudah ada
            $headResident->update($residentData);
        } else {
            // Create baru
            Resident::create(array_merge($residentData, [
                'household_id' => $household->id,
            ]));
        }
    }
}
