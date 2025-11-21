<?php

namespace App\Console\Commands;

use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUsersForHouseholds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'households:create-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user login for all household heads that don\'t have users yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Creating Users for Household Heads ===');
        $this->newLine();

        $households = Household::with('residents')->get();
        $created = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($households->count());

        foreach ($households as $household) {
            $progressBar->advance();

            // Find head resident
            $headResident = $household->residents()
                ->where('relationship', 'Kepala Keluarga')
                ->where('status', Resident::STATUS_ACTIVE)
                ->first();

            if (!$headResident) {
                $skipped++;
                continue;
            }

            // Already has user
            if ($headResident->user_id) {
                $skipped++;
                continue;
            }

            try {
                // Check if family_card_number already used
                $existingUser = User::where('family_card_number', $household->family_card_number)->first();

                if ($existingUser) {
                    // Link to existing user
                    $headResident->update(['user_id' => $existingUser->id]);
                    $skipped++;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $household->head_name,
                        'email' => $household->head_email ?? "kk{$household->family_card_number}@sipakrt.local",
                        'family_card_number' => $household->family_card_number,
                        'password' => Hash::make('password123'),
                        'is_admin' => false,
                        'role' => 'warga',
                    ]);

                    // Link to resident
                    $headResident->update(['user_id' => $user->id]);
                    $created++;
                }
            } catch (\Exception $e) {
                $this->error("\nError for KK {$household->family_card_number}: {$e->getMessage()}");
                $errors++;
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('=== Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Households', $households->count()],
                ['Users Created', $created],
                ['Skipped', $skipped],
                ['Errors', $errors],
            ]
        );

        $this->newLine();
        $this->comment('Default password for all users: password123');
        $this->comment('Users can login using their No. KK (family card number)');

        return Command::SUCCESS;
    }
}
