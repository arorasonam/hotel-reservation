<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {
            if (! Schema::hasColumn('reservation_folios', 'reservation_room_detail_id')) {
                $table->foreignId('reservation_room_detail_id')
                    ->nullable()
                    ->after('reservation_room_id')
                    ->constrained('reservation_room_details')
                    ->nullOnDelete();
            }
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_orders', 'reservation_room_detail_id')) {
                $table->foreignId('reservation_room_detail_id')
                    ->nullable()
                    ->after('reservation_room_id')
                    ->constrained('reservation_room_details')
                    ->nullOnDelete();
            }
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_payments', 'reservation_room_detail_id')) {
                $table->foreignId('reservation_room_detail_id')
                    ->nullable()
                    ->after('reservation_room_id')
                    ->constrained('reservation_room_details')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            if (Schema::hasColumn('pos_payments', 'reservation_room_detail_id')) {
                $table->dropConstrainedForeignId('reservation_room_detail_id');
            }
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'reservation_room_detail_id')) {
                $table->dropConstrainedForeignId('reservation_room_detail_id');
            }
        });

        Schema::table('reservation_folios', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_folios', 'reservation_room_detail_id')) {
                $table->dropConstrainedForeignId('reservation_room_detail_id');
            }
        });
    }
};
