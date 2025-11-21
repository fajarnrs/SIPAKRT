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
            $table->string('email')->nullable()->after('leader_name');
        });

        Schema::table('households', function (Blueprint $table) {
            $table->string('head_email')->nullable()->after('head_status');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->string('email')->nullable()->after('marital_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('password');
            $table->foreignId('rt_id')->nullable()->after('role')->constrained('rts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rts', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn('head_email');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rt_id');
            $table->dropColumn('role');
        });
    }
};
