<?php

use App\Services\TaxService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            if (! Schema::hasColumn('taxes', 'type')) {
                $table->string('type')->default(TaxService::TYPE_GST)->after('name')->index();
            }
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('tax_id')->constrained('taxes')->cascadeOnDelete();
            $table->string('name');
            $table->string('applies_to')->index();
            $table->string('item_tax_category')->nullable()->index();
            $table->decimal('min_tariff', 10, 2)->nullable();
            $table->decimal('max_tariff', 10, 2)->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::table('pos_items', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_items', 'tax_category')) {
                $table->string('tax_category')->default(TaxService::ITEM_CATEGORY_STANDARD_RESTAURANT)->after('price')->index();
            }
        });

        Schema::table('reservation_folios', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable()->after('amount')->constrained('taxes')->nullOnDelete();
            $table->foreignId('tax_rule_id')->nullable()->after('tax_id')->constrained('tax_rules')->nullOnDelete();
            $table->string('tax_name')->nullable()->after('tax_rule_id');
            $table->string('tax_type')->nullable()->after('tax_name')->index();
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('tax_type');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_percentage');
        });

        $this->seedTaxRules();
    }

    public function down(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_id');
            $table->dropConstrainedForeignId('tax_rule_id');
            $table->dropColumn([
                'tax_name',
                'tax_type',
                'tax_percentage',
                'tax_amount',
            ]);
        });

        Schema::table('pos_items', function (Blueprint $table) {
            if (Schema::hasColumn('pos_items', 'tax_category')) {
                $table->dropColumn('tax_category');
            }
        });

        Schema::dropIfExists('tax_rules');

        Schema::table('taxes', function (Blueprint $table) {
            if (Schema::hasColumn('taxes', 'type')) {
                $table->dropColumn('type');
            }
        });
    }

    private function seedTaxRules(): void
    {
        $gst0 = $this->taxId('GST Exempt', TaxService::TYPE_GST, 0);
        $gst5 = $this->taxId('GST 5%', TaxService::TYPE_GST, 5);
        $gst12 = $this->taxId('GST 12%', TaxService::TYPE_GST, 12);
        $gst18 = $this->taxId('GST 18%', TaxService::TYPE_GST, 18);
        $gst28 = $this->taxId('GST 28%', TaxService::TYPE_GST, 28);

        $rules = [
            ['tax_id' => $gst0, 'name' => 'Accommodation up to 1000', 'applies_to' => TaxService::APPLIES_TO_ACCOMMODATION, 'item_tax_category' => null, 'min_tariff' => null, 'max_tariff' => 1000, 'priority' => 100],
            ['tax_id' => $gst5, 'name' => 'Accommodation 1001 to 7500', 'applies_to' => TaxService::APPLIES_TO_ACCOMMODATION, 'item_tax_category' => null, 'min_tariff' => 1000.01, 'max_tariff' => 7500, 'priority' => 100],
            ['tax_id' => $gst18, 'name' => 'Accommodation above 7500', 'applies_to' => TaxService::APPLIES_TO_ACCOMMODATION, 'item_tax_category' => null, 'min_tariff' => 7500.01, 'max_tariff' => null, 'priority' => 100],
            ['tax_id' => $gst18, 'name' => 'Restaurant in luxury hotel', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => TaxService::ITEM_CATEGORY_STANDARD_RESTAURANT, 'min_tariff' => 7500, 'max_tariff' => null, 'priority' => 200],
            ['tax_id' => $gst5, 'name' => 'Standard restaurant', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => TaxService::ITEM_CATEGORY_STANDARD_RESTAURANT, 'min_tariff' => null, 'max_tariff' => null, 'priority' => 100],
            ['tax_id' => $gst5, 'name' => 'Packaged food', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => 'packaged_food', 'min_tariff' => null, 'max_tariff' => null, 'priority' => 150],
            ['tax_id' => $gst12, 'name' => 'Processed food 12%', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => 'processed_food_12', 'min_tariff' => null, 'max_tariff' => null, 'priority' => 150],
            ['tax_id' => $gst18, 'name' => 'Processed food 18%', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => 'processed_food_18', 'min_tariff' => null, 'max_tariff' => null, 'priority' => 150],
            ['tax_id' => $gst28, 'name' => 'Aerated drinks and luxury items', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => 'aerated_drinks_luxury', 'min_tariff' => null, 'max_tariff' => null, 'priority' => 150],
            ['tax_id' => $gst0, 'name' => 'Exempt fresh staples', 'applies_to' => TaxService::APPLIES_TO_RESTAURANT, 'item_tax_category' => 'fresh_staples', 'min_tariff' => null, 'max_tariff' => null, 'priority' => 150],
        ];

        foreach ($rules as $rule) {
            DB::table('tax_rules')->updateOrInsert(
                ['name' => $rule['name']],
                $rule + [
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function taxId(string $name, string $type, float $percentage): int
    {
        DB::table('taxes')->updateOrInsert(
            [
                'name' => $name,
                'type' => $type,
            ],
            [
                'percentage' => $percentage,
                'status' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        return (int) DB::table('taxes')
            ->where('name', $name)
            ->where('type', $type)
            ->value('id');
    }
};
