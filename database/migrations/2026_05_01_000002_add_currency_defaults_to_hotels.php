<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (! Schema::hasColumn('hotels', 'base_currency_code')) {
                $table->string('base_currency_code', 3)->default('INR')->after('is_active');
                $table->foreign('base_currency_code')->references('code')->on('currencies');
            }
        });

        DB::table('hotels')->whereNull('base_currency_code')->update([
            'base_currency_code' => 'INR',
        ]);
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'base_currency_code')) {
                $table->dropForeign(['base_currency_code']);
                $table->dropColumn('base_currency_code');
            }
        });
    }
};
