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
        Schema::table('overtimes', function (Blueprint $table) {
            $table->time('actual_time_start')->nullable()->change();
            $table->time('actual_time_end')->nullable()->change();
            $table->decimal('actual_time_taken', 8, 2)->nullable()->change();
            $table->decimal('actual_time_approved', 8, 2)->nullable()->change();
            $table->string('claim_as')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->time('actual_time_start')->change();
            $table->time('actual_time_end')->change();
            $table->decimal('actual_time_taken', 8, 2)->change();
            $table->decimal('actual_time_approved', 8, 2)->change();
            $table->string('claim_as')->change();
        });
    }
};
