<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Use foreignUuid if your Hotel model uses UUIDs, 
            // otherwise use foreignId for standard integers.
            $table->foreignUuid('hotel_id')
                ->nullable()
                ->after('id')
                ->constrained('hotels')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');
        });
    }
};
