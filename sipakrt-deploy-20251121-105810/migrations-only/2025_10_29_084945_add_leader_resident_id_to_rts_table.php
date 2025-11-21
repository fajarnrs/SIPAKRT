<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rts', function (Blueprint $table) {
            $table->foreignId('leader_resident_id')->nullable()->after('leader_name')->constrained('residents')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leader_resident_id');
        });
    }
};
