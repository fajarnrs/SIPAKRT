#!/usr/bin/env php
<?php

/**
 * Script untuk memperbaiki KK yang tidak punya resident kepala keluarga
 * Menambahkan resident kepala keluarga dari data field head_* di tabel households
 * 
 * Usage: php scripts/repair-missing-head-residents.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Household;
use App\Models\Resident;

echo "═══════════════════════════════════════════════════════════════\n";
echo "   REPAIR SCRIPT - Tambah Resident Kepala Keluarga           \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Cari KK yang punya head_name tapi tidak punya resident kepala keluarga
$householdsWithoutHead = Household::whereNotNull('head_name')
    ->whereDoesntHave('residents', function ($query) {
        $query->where('relationship', 'Kepala Keluarga');
    })
    ->get();

$total = $householdsWithoutHead->count();

if ($total === 0) {
    echo "✓ Tidak ada KK yang perlu diperbaiki!\n";
    echo "  Semua KK sudah punya resident kepala keluarga.\n\n";
    exit(0);
}

echo "Ditemukan {$total} KK tanpa resident kepala keluarga:\n\n";

$fixed = 0;
$errors = 0;

foreach ($householdsWithoutHead as $household) {
    echo "Processing KK: {$household->family_card_number} ({$household->head_name})... ";
    
    try {
        // Jika head_status bukan aktif, skip
        if (($household->head_status ?? Resident::STATUS_ACTIVE) !== Resident::STATUS_ACTIVE) {
            echo "SKIPPED (status: {$household->head_status})\n";
            continue;
        }

        // Create resident kepala keluarga
        $resident = Resident::create([
            'household_id' => $household->id,
            'name' => $household->head_name,
            'nik' => $household->head_nik,
            'relationship' => 'Kepala Keluarga',
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
        ]);

        echo "✓ CREATED (ID: {$resident->id})\n";
        $fixed++;
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "SUMMARY:\n";
echo "  Total KK processed: {$total}\n";
echo "  Successfully fixed: {$fixed}\n";
echo "  Errors: {$errors}\n";
echo "═══════════════════════════════════════════════════════════════\n";

exit($errors > 0 ? 1 : 0);
