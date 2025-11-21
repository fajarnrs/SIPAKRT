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
        Schema::create('guest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_id_number')->nullable();
            $table->string('origin');
            $table->string('purpose')->nullable();
            $table->date('visit_date');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_logs');
    }
};
