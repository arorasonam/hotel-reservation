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
       Schema::create('pos_orders', function (Blueprint $table) {

            $table->id();

            $table->foreignUuid('hotel_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('guest_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->uuid('room_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('pos_outlet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('order_number')->unique();

            $table->enum('order_type', [
                'room_charge',
                'walk_in',
                'takeaway'
            ]);

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);

            $table->enum('status', [
                'draft',
                'confirmed',
                'paid',
                'cancelled'
            ])->default('draft');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
