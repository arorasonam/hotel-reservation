<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('pos_order_items', 'gst_tax_id')) {
                $table->dropConstrainedForeignId('gst_tax_id');
            }

            if (Schema::hasColumn('pos_order_items', 'gst_tax_rule_id')) {
                $table->dropConstrainedForeignId('gst_tax_rule_id');
            }

            if (Schema::hasColumn('pos_order_items', 'vat_tax_id')) {
                $table->dropConstrainedForeignId('vat_tax_id');
            }

            if (Schema::hasColumn('pos_order_items', 'vat_tax_rule_id')) {
                $table->dropConstrainedForeignId('vat_tax_rule_id');
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('pos_order_items', 'gst_tax_name') ? 'gst_tax_name' : null,
                Schema::hasColumn('pos_order_items', 'gst_percentage') ? 'gst_percentage' : null,
                Schema::hasColumn('pos_order_items', 'gst_amount') ? 'gst_amount' : null,
                Schema::hasColumn('pos_order_items', 'vat_tax_name') ? 'vat_tax_name' : null,
                Schema::hasColumn('pos_order_items', 'vat_percentage') ? 'vat_percentage' : null,
                Schema::hasColumn('pos_order_items', 'vat_amount') ? 'vat_amount' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void {}
};
