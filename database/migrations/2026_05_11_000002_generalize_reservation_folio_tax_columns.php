<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_folios', 'gst_tax_id')) {
                $table->dropConstrainedForeignId('gst_tax_id');
            }

            if (Schema::hasColumn('reservation_folios', 'gst_tax_rule_id')) {
                $table->dropConstrainedForeignId('gst_tax_rule_id');
            }

            if (Schema::hasColumn('reservation_folios', 'vat_tax_id')) {
                $table->dropConstrainedForeignId('vat_tax_id');
            }

            if (Schema::hasColumn('reservation_folios', 'vat_tax_rule_id')) {
                $table->dropConstrainedForeignId('vat_tax_rule_id');
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('reservation_folios', 'gst_tax_name') ? 'gst_tax_name' : null,
                Schema::hasColumn('reservation_folios', 'gst_percentage') ? 'gst_percentage' : null,
                Schema::hasColumn('reservation_folios', 'gst_amount') ? 'gst_amount' : null,
                Schema::hasColumn('reservation_folios', 'vat_tax_name') ? 'vat_tax_name' : null,
                Schema::hasColumn('reservation_folios', 'vat_percentage') ? 'vat_percentage' : null,
                Schema::hasColumn('reservation_folios', 'vat_amount') ? 'vat_amount' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('reservation_folios', function (Blueprint $table) {
            if (! Schema::hasColumn('reservation_folios', 'tax_id')) {
                $table->foreignId('tax_id')->nullable()->after('amount')->constrained('taxes')->nullOnDelete();
            }

            if (! Schema::hasColumn('reservation_folios', 'tax_rule_id')) {
                $table->foreignId('tax_rule_id')->nullable()->after('tax_id')->constrained('tax_rules')->nullOnDelete();
            }

            if (! Schema::hasColumn('reservation_folios', 'tax_name')) {
                $table->string('tax_name')->nullable()->after('tax_rule_id');
            }

            if (! Schema::hasColumn('reservation_folios', 'tax_type')) {
                $table->string('tax_type')->nullable()->after('tax_name')->index();
            }

            if (! Schema::hasColumn('reservation_folios', 'tax_percentage')) {
                $table->decimal('tax_percentage', 5, 2)->nullable()->after('tax_type');
            }

            if (! Schema::hasColumn('reservation_folios', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_percentage');
            }
        });
    }

    public function down(): void {}
};
