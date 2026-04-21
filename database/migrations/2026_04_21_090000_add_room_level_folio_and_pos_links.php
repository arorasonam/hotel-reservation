<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('reservation_rooms', 'status')) {
                $table->string('status')->default('confirmed')->after('room_number')->index();
            }

            if (! Schema::hasColumn('reservation_rooms', 'check_in')) {
                $table->date('check_in')->nullable()->after('status');
            }

            if (! Schema::hasColumn('reservation_rooms', 'check_out')) {
                $table->date('check_out')->nullable()->after('check_in');
            }

            if (! Schema::hasColumn('reservation_rooms', 'rate')) {
                $table->decimal('rate', 10, 2)->default(0)->after('check_out');
            }

            if (! Schema::hasColumn('reservation_rooms', 'nights')) {
                $table->unsignedSmallInteger('nights')->default(1)->after('rate');
            }

            if (! Schema::hasColumn('reservation_rooms', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('nights');
            }

            if (! Schema::hasColumn('reservation_rooms', 'checked_out_at')) {
                $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');
            }
        });

        Schema::table('reservation_folios', function (Blueprint $table) {
            if (! Schema::hasColumn('reservation_folios', 'reservation_room_id')) {
                $table->foreignId('reservation_room_id')
                    ->nullable()
                    ->after('reservation_id')
                    ->constrained('reservation_rooms')
                    ->nullOnDelete();
            }
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_orders', 'reservation_room_id')) {
                $table->foreignId('reservation_room_id')
                    ->nullable()
                    ->after('reservation_id')
                    ->constrained('reservation_rooms')
                    ->nullOnDelete();
            }
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_payments', 'reservation_room_id')) {
                $table->foreignId('reservation_room_id')
                    ->nullable()
                    ->after('reservation_id')
                    ->constrained('reservation_rooms')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            if (Schema::hasColumn('pos_payments', 'reservation_room_id')) {
                $table->dropConstrainedForeignId('reservation_room_id');
            }
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'reservation_room_id')) {
                $table->dropConstrainedForeignId('reservation_room_id');
            }
        });

        Schema::table('reservation_folios', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_folios', 'reservation_room_id')) {
                $table->dropConstrainedForeignId('reservation_room_id');
            }
        });

        Schema::table('reservation_rooms', function (Blueprint $table) {
            $columns = [
                'status',
                'check_in',
                'check_out',
                'rate',
                'nights',
                'checked_in_at',
                'checked_out_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('reservation_rooms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
