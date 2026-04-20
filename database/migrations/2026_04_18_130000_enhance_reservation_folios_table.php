<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {
            $table->string('source_key')->nullable()->after('source_id');
            $table->string('reference')->nullable()->after('description');
            $table->text('notes')->nullable()->after('reference');
            $table->enum('entry_type', ['charge', 'tax', 'discount', 'payment', 'refund'])
                ->default('charge')
                ->after('amount');

            $table->index(['reservation_id', 'posted_at']);
            $table->index(['source', 'source_id', 'source_key']);
        });
    }

    public function down(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {
            $table->dropIndex(['reservation_id', 'posted_at']);
            $table->dropIndex(['source', 'source_id', 'source_key']);
            $table->dropColumn([
                'source_key',
                'reference',
                'notes',
                'entry_type',
            ]);
        });
    }
};
