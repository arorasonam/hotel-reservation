<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_category_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pos_category_id', 'tax_id']);
        });

        DB::table('pos_categories')
            ->whereNotNull('tax_id')
            ->orderBy('id')
            ->each(function (object $category): void {
                DB::table('pos_category_tax')->insertOrIgnore([
                    'pos_category_id' => $category->id,
                    'tax_id' => $category->tax_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_category_tax');
    }
};
