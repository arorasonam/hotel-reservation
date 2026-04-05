<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Run this migration to add the columns the calendar expects.
 *
 * php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── reservations table ──────────────────────────────────
        Schema::table('reservations', function (Blueprint $table) {

            if (! Schema::hasColumn('reservations', 'status')) {
                $table->string('status')->default('confirmed')->index();
                // Values used by the calendar:
                // confirmed | tentative | waitlist | checked_in | cancelled
            }
            if (! Schema::hasColumn('reservations', 'room_no')) {
                $table->string('room_no')->nullable();
            }
            if (! Schema::hasColumn('reservations', 'check_in')) {
                $table->date('check_in')->nullable();
            }
            if (! Schema::hasColumn('reservations', 'check_out')) {
                $table->date('check_out')->nullable();
            }
            if (! Schema::hasColumn('reservations', 'nights')) {
                $table->unsignedSmallInteger('nights')->default(1);
            }
            if (! Schema::hasColumn('reservations', 'adults')) {
                $table->unsignedTinyInteger('adults')->default(1);
            }
            if (! Schema::hasColumn('reservations', 'title')) {
                $table->string('title', 10)->nullable();   // Mr. Mrs. Ms. Dr.
            }
            if (! Schema::hasColumn('reservations', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (! Schema::hasColumn('reservations', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (! Schema::hasColumn('reservations', 'email')) {
                $table->string('email')->nullable();
            }
            if (! Schema::hasColumn('reservations', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
            if (! Schema::hasColumn('reservations', 'rate')) {
                $table->decimal('rate', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('reservations', 'outstanding')) {
                $table->decimal('outstanding', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('reservations', 'source')) {
                $table->string('source')->nullable();   // Direct | Booking.com | OTA …
            }
            if (! Schema::hasColumn('reservations', 'ref_id')) {
                $table->string('ref_id')->nullable();
            }
        });

        // ── hotel_rooms table ───────────────────────────────────
        // (also called 'rooms' depending on your migration — adjust the table name below)
        $roomsTable = 'hotel_rooms'; // ← change to 'rooms' if that's your table name

        if (Schema::hasTable($roomsTable)) {
            Schema::table($roomsTable, function (Blueprint $table) use ($roomsTable) {
                if (! Schema::hasColumn($roomsTable, 'status')) {
                    $table->string('status')->default('clean')->index();
                    // Values: clean | dirty | mnt | ooo | complaint | sanitised | vip | inspect | discrepancy
                }
            });
        }
    }

    public function down(): void
    {
        // Only drop columns this migration added — be careful not to drop pre-existing ones.
        // In practice, comment out any column your original migration already created.

        $resCols = [
            'status', 'room_no', 'check_in', 'check_out', 'nights',
            'adults', 'title', 'first_name', 'last_name', 'email',
            'phone', 'rate', 'outstanding', 'source', 'ref_id',
        ];

        Schema::table('reservations', function (Blueprint $table) use ($resCols) {
            $existing = array_filter($resCols, fn($c) => Schema::hasColumn('reservations', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });

        $roomsTable = 'hotel_rooms';
        if (Schema::hasTable($roomsTable) && Schema::hasColumn($roomsTable, 'status')) {
            Schema::table($roomsTable, fn(Blueprint $t) => $t->dropColumn('status'));
        }
    }
};
