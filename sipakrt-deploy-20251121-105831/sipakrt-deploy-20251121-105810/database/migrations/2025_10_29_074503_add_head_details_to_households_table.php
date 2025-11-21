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
        Schema::table('households', function (Blueprint $table) {
            $table->string('head_nik')->nullable()->after('head_name');
            $table->string('head_gender', 10)->nullable()->after('head_nik');
            $table->string('head_birth_place')->nullable()->after('head_gender');
            $table->date('head_birth_date')->nullable()->after('head_birth_place');
            $table->string('head_religion')->nullable()->after('head_birth_date');
            $table->string('head_education')->nullable()->after('head_religion');
            $table->string('head_occupation')->nullable()->after('head_education');
            $table->string('head_marital_status')->nullable()->after('head_occupation');
            $table->string('head_nationality')->nullable()->after('head_marital_status');
            $table->string('head_status')->nullable()->after('head_nationality');
            $table->text('head_notes')->nullable()->after('head_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn([
                'head_nik',
                'head_gender',
                'head_birth_place',
                'head_birth_date',
                'head_religion',
                'head_education',
                'head_occupation',
                'head_marital_status',
                'head_nationality',
                'head_status',
                'head_notes',
            ]);
        });
    }
};
