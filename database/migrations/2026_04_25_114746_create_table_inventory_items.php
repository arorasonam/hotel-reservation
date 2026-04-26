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
       Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->char('hotel_id', 36);
            $table->string('name');
            $table->string('unit'); // kg, pcs, ml
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('reorder_level', 12, 3)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_inventory_items');
    }
};
