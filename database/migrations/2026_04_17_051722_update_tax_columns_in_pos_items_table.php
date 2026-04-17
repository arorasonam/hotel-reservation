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
       Schema::table('pos_items', function (Blueprint $table) {

            // Rename column tax → tax_amount
            $table->renameColumn('tax_percentage', 'tax_amount');

            // Add new foreign key column tax_id
            $table->foreignId('tax_id')
                ->nullable()
                ->constrained('taxes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_items', function (Blueprint $table) {

            // Drop foreign key first   
            $table->dropForeign(['tax_id']);

            // Drop column
            $table->dropColumn('tax_id');

            // Rename back
            $table->renameColumn('tax_amount', 'tax');
        });
    }
};
