<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->string('table_no')
                ->nullable()
                ->after('pos_outlet_id');

            $table->timestamp('settled_at')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn([
                'table_no',
                'settled_at',
            ]);
        });
    }
};
