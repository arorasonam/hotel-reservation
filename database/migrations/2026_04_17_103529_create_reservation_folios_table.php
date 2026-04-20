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
        Schema::create('reservation_folios', function (Blueprint $table) {

        $table->id();

        $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

        $table->string('source'); 
        // pos / laundry / spa / minibar / room_charge

        $table->unsignedBigInteger('source_id')->nullable();
        // pos_order_id

        $table->string('description');

        $table->decimal('amount', 10, 2);

        $table->enum('type', ['debit', 'credit'])->default('debit');

        $table->timestamp('posted_at')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_folios');
    }
};
