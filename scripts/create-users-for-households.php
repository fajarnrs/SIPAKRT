<?php

/**
 * Script untuk membuat user login untuk semua kepala keluarga yang belum punya user
 * 
 * Cara pakai:
 * php artisan tinker < scripts/create-users-for-households.php
 */

use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Membuat User untuk Kepala Keluarga ===\n\n";

$households = Household::with('residents')->get();
$created = 0;
$skipped = 0;
$errors = 0;

foreach ($households as $household) {
    echo "Processing KK: {$household->family_card_number} - {$household->head_name}\n";
    
    // Cari resident kepala keluarga
    $headResident = $household->residents()
        ->where('relationship', 'Kepala Keluarga')
        ->where('status', Resident::STATUS_ACTIVE)
        ->first();
    
    if (!$headResident) {
        echo "  ⚠ No active head resident found\n";
        $skipped++;
        continue;
    }
    
    // Cek apakah sudah punya user
    if ($headResident->user_id) {
        echo "  ℹ Already has user (ID: {$headResident->user_id})\n";
        $skipped++;
        continue;
    }
    
    try {
        // Cek apakah No. KK sudah dipakai user lain
        $existingUser = User::where('family_card_number', $household->family_card_number)->first();
        
        if ($existingUser) {
            echo "  ↻ Linking to existing user (ID: {$existingUser->id})\n";
            $headResident->update(['user_id' => $existingUser->id]);
            $skipped++;
        } else {
            // Buat user baru
            $user = User::create([
                'name' => $household->head_name,
                'email' => $household->head_email ?? "kk{$household->family_card_number}@sipakrt.local",
                'family_card_number' => $household->family_card_number,
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]);
            
            // Link ke resident
            $headResident->update(['user_id' => $user->id]);
            
            echo "  ✓ User created (ID: {$user->id}) - Login: {$household->family_card_number}\n";
            $created++;
        }
    } catch (Exception $e) {
        echo "  ✗ Error: {$e->getMessage()}\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Total households: " . count($households) . "\n";
echo "Users created: {$created}\n";
echo "Skipped: {$skipped}\n";
echo "Errors: {$errors}\n";
echo "\nDefault password for all users: password123\n";
echo "Login using No. KK (family card number)\n";
