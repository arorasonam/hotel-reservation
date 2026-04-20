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
        Schema::table('pos_order_items', function (Blueprint $table) {
            // Rename column tax → tax_amount
            $table->renameColumn('tax', 'tax_amount');
            // add columns //
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();

            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
           // Drop foreign key first   
            $table->dropColumn(['tax_percentage']);

            // Drop column
            $table->dropColumn('subtotal');
            $table->dropColumn('tax_id');
            // Rename back
            $table->renameColumn('tax_amount', 'tax');
        });
    }
};
