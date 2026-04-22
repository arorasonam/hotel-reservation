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
        Schema::table('reservations', function (Blueprint $table) {
            // Inclusions
            if (!Schema::hasColumn('reservations', 'type')) $table->string('type')->nullable();
            if (!Schema::hasColumn('reservations', 'rate_plan')) $table->string('rate_plan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['type', 'rate_plan']);
        });
    }
};
