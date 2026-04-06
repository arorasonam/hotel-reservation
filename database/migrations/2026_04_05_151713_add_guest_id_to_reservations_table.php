<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'guest_id')) {
                $table->unsignedBigInteger('guest_id')->nullable()->after('id');
                $table->unsignedBigInteger('room_type_id')->nullable()->after('guest_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('guest_id');
            $table->dropColumn('room_type_id');
        });
    }
};
