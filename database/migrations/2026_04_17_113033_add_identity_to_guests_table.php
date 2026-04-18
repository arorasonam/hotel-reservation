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
        Schema::table('guests', function (Blueprint $table) {
            $table->string('identity_type')->nullable()->after('nationality');
            $table->string('identity_number')->nullable()->after('identity_type');
            $table->string('identity_document')->nullable()->after('identity_number');
            $table->date('identity_expiry')->nullable()->after('identity_document');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            //
        });
    }
};
