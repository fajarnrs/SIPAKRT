<?php

namespace App\Observers;

use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        // Sync head resident jika ada perubahan field kepala keluarga
        if ($household->wasChanged([
            'head_name', 'head_nik', 'head_gender', 'head_birth_place', 
            'head_birth_date', 'head_religion', 'head_education', 
            'head_occupation', 'head_marital_status', 'head_status', 'head_email'
        ])) {
            $this->syncHeadResident($household);
        }
        
        // Juga sync user jika ada perubahan No. KK
        if ($household->wasChanged('family_card_number')) {
            $headResident = Resident::where('household_id', $household->id)
                ->where('relationship', 'Kepala Keluarga')
                ->first();
            
            if ($headResident) {
                $this->createUserForHeadResident($headResident, $household);
            }
        }
    }

    /**
     * Handle the Household "deleting" event (before delete).
     */
    public function deleting(Household $household): void
    {
        // Hapus user login kepala keluarga sebelum KK dihapus
        $headResident = Resident::where('household_id', $household->id)
            ->where('relationship', 'Kepala Keluarga')
            ->first();
        
        if ($headResident && $headResident->user_id) {
            User::where('id', $headResident->user_id)->delete();
        }
        
        // Hapus semua residents di KK ini
        Resident::where('household_id', $household->id)->delete();
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
            $headResident = Resident::create(array_merge($residentData, [
                'household_id' => $household->id,
            ]));
        }
        
        // Auto-create/update user login untuk kepala keluarga
        $this->createUserForHeadResident($headResident, $household);
    }
    
    /**
     * Create user account untuk kepala keluarga
     */
    protected function createUserForHeadResident(Resident $headResident, Household $household): void
    {
        // Selalu buat/update user untuk kepala keluarga dengan No. KK
        if (!$headResident->user_id) {
            // Cek apakah No. KK sudah dipakai user lain
            $existingUser = User::where('family_card_number', $household->family_card_number)->first();
            
            if (!$existingUser) {
                // Buat user baru dengan No. KK sebagai username
                $user = User::create([
                    'name' => $household->head_name,
                    'email' => $household->head_email,
                    'family_card_number' => $household->family_card_number,
                    'password' => Hash::make('password123'), // Default password
                    'is_admin' => false,
                    'role' => 'warga', // Warga biasa, bukan admin
                ]);
                
                // Link user ke resident
                $headResident->update(['user_id' => $user->id]);
            } else {
                // Link ke user yang sudah ada
                $headResident->update(['user_id' => $existingUser->id]);
            }
        } else {
            // Update user yang sudah ada
            $user = User::find($headResident->user_id);
            if ($user) {
                $user->update([
                    'name' => $household->head_name,
                    'email' => $household->head_email,
                    'family_card_number' => $household->family_card_number,
                ]);
            }
        }
    }
}
