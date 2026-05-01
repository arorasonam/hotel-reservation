<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->foreignUuid('country_id')
                ->nullable()
                ->after('id')
                ->constrained('countries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
