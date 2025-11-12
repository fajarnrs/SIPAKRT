<?php

namespace Database\Seeders;

use App\Models\AssistanceProgram;
use App\Models\AssistanceRecipient;
use App\Models\GuestLog;
use App\Models\Household;
use App\Models\HouseholdMovement;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\RtOfficial;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        AssistanceRecipient::query()->delete();
        AssistanceProgram::query()->delete();
        GuestLog::query()->delete();
        HouseholdMovement::query()->delete();
        Vehicle::query()->delete();
        Resident::query()->delete();
        Household::query()->delete();
        Rt::query()->delete();
        Schema::enableForeignKeyConstraints();

        $admin = User::first();

        Rt::factory()
            ->count(5)
            ->create()
            ->each(function (Rt $rt) use ($admin): void {
                Household::factory()
                    ->count(rand(3, 6))
                    ->create(['rt_id' => $rt->id])
                    ->each(function (Household $household) use ($admin): void {
                        Resident::factory()
                            ->for($household)
                            ->state(function () use ($household): array {
                                return [
                                    'name' => $household->head_name,
                                    'nik' => $household->head_nik,
                                    'relationship' => 'Kepala Keluarga',
                                    'gender' => $household->head_gender ?? 'male',
                                    'birth_place' => $household->head_birth_place,
                                    'birth_date' => $household->head_birth_date,
                                    'religion' => $household->head_religion,
                                    'education' => $household->head_education,
                                    'occupation' => $household->head_occupation,
                                    'marital_status' => $household->head_marital_status,
                                    'nationality' => $household->head_nationality ?? 'WNI',
                                    'status' => $household->head_status ?? Resident::STATUS_ACTIVE,
                                    'status_effective_at' => null,
                                    'email' => $household->head_email,
                                    'notes' => $household->head_notes,
                                ];
                            })
                            ->create();

                        Resident::factory()
                            ->count(rand(1, 4))
                            ->for($household)
                            ->create();

                        Vehicle::factory()
                            ->count(rand(0, 2))
                            ->for($household)
                            ->create();

                        HouseholdMovement::factory()
                            ->count(rand(0, 1))
                            ->for($household)
                            ->state(function () use ($admin): array {
                                return [
                                    'processed_by' => $admin?->id,
                                ];
                            })
                            ->create();
                    });

                                $leaderId = $rt->residents()
                    ->select('residents.id')
                    ->where('relationship', 'Kepala Keluarga')
                    ->inRandomOrder()
                    ->value('residents.id');

                if ($leaderId) {
                    RtOfficial::create([
                        'rt_id' => $rt->id,
                        'resident_id' => $leaderId,
                        'position' => 'Ketua RT',
                        'started_at' => now()->subYears(rand(0, 3))->startOfYear(),
                        'notes' => 'Data demo otomatis',
                    ]);
                }

GuestLog::factory()
                    ->count(rand(2, 5))
                    ->for($rt)
                    ->state(function () use ($rt): array {
                        $residentId = $rt->residents()->select('residents.id')->inRandomOrder()->value('residents.id');

                        return [
                            'resident_id' => $residentId,
                        ];
                    })
                    ->create();
            });

        AssistanceProgram::factory()
            ->count(3)
            ->create()
            ->each(function (AssistanceProgram $program): void {
                $households = Household::inRandomOrder()->take(rand(2, 5))->get();

                foreach ($households as $household) {
                    AssistanceRecipient::factory()
                        ->for($program, 'program')
                        ->for($household)
                        ->state([
                            'resident_id' => $household->residents()->inRandomOrder()->value('id'),
                        ])
                        ->create();
                }
            });
    }
}
