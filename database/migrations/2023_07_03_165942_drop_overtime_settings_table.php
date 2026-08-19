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
        Schema::table('overtime_settings', function (Blueprint $table) {
            Schema::dropIfExists('overtime_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtime_settings', function (Blueprint $table) {
            $table->id();
            $table->time('ot_start_time')->nullable();
            $table->time('ot_end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
