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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            $table->string('from_currency', 3);
            $table->string('to_currency', 3);

            $table->decimal('rate', 18, 8);

            $table->dateTime('effective_from');

            $table->enum('source', ['manual', 'api'])->default('manual');

            $table->timestamps();

            $table->unique(['from_currency', 'to_currency', 'effective_from'], 'unique_rate');
            $table->index(['from_currency', 'to_currency'], 'idx_from_to');
            $table->index('effective_from', 'idx_effective_from');
            $table->foreign('from_currency')->references('code')->on('currencies');
            $table->foreign('to_currency')->references('code')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
