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
        Schema::table('claim_types', function (Blueprint $table) {
            $table->dropColumn('amount_unit');
            $table->string('unit')->after('description');
            $table->boolean('unit_price')->after('unit')->default(false);
            $table->decimal('unit_price_value', 10, 2)->after('unit_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_types', function (Blueprint $table) {
            $table->string('amount_unit')->after('description');
            $table->dropColumn('unit');
            $table->dropColumn('unit_price');
            $table->dropColumn('unit_price_value');
        });
    }
};
