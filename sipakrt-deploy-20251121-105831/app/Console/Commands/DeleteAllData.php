<?php

namespace App\Console\Commands;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Console\Command;

class DeleteAllData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:delete-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all RT, KK, Residents, and their associated users data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠️  WARNING: This will delete ALL data!');
        $this->newLine();
        
        // Count data
        $rtCount = Rt::count();
        $kkCount = Household::count();
        $residentCount = Resident::count();
        $userCount = User::whereNotNull('family_card_number')->count();
        
        $this->table(
            ['Data Type', 'Count'],
            [
                ['RT', $rtCount],
                ['Kartu Keluarga (KK)', $kkCount],
                ['Warga (Residents)', $residentCount],
                ['User Login (Kepala KK)', $userCount],
            ]
        );
        
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete ALL data? This cannot be undone!')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }
        
        $this->info('Deleting data...');
        $progressBar = $this->output->createProgressBar(4);
        
        // 1. Delete households (will trigger observer to delete residents & users)
        $progressBar->advance();
        $deletedKK = Household::count();
        Household::query()->delete();
        $this->newLine();
        $this->info("✓ Deleted {$deletedKK} Kartu Keluarga (KK)");
        
        // 2. Delete any remaining residents
        $progressBar->advance();
        $deletedResidents = Resident::count();
        Resident::query()->delete();
        $this->info("✓ Deleted {$deletedResidents} remaining Residents");
        
        // 3. Delete household users
        $progressBar->advance();
        $deletedUsers = User::whereNotNull('family_card_number')->count();
        User::whereNotNull('family_card_number')->delete();
        $this->info("✓ Deleted {$deletedUsers} User logins");
        
        // 4. Delete RT
        $progressBar->advance();
        $deletedRT = Rt::count();
        Rt::query()->delete();
        $this->info("✓ Deleted {$deletedRT} RT");
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info('✅ All data deleted successfully!');
        $this->newLine();
        
        // Verify
        $this->table(
            ['Data Type', 'Remaining'],
            [
                ['RT', Rt::count()],
                ['Kartu Keluarga (KK)', Household::count()],
                ['Warga (Residents)', Resident::count()],
                ['User Login (Kepala KK)', User::whereNotNull('family_card_number')->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
