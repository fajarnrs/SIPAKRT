<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_movements', function (Blueprint $table): void {
            $table->foreignId('affected_resident_id')
                ->nullable()
                ->after('household_id')
                ->constrained('residents')
                ->nullOnDelete();

            $table->json('metadata')->nullable()->after('destination');
        });
    }

    public function down(): void
    {
        Schema::table('household_movements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('affected_resident_id');
            $table->dropColumn('metadata');
        });
    }
};
