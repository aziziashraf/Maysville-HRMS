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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('carry_forward_timeframe_type')->nullable()->after('carry_forward_limit');
            $table->integer('carry_forward_timeframe_value')->nullable()->after('carry_forward_timeframe_type');
            $table->dropColumn('carry_forward_expiry_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('carry_forward_timeframe_type');
            $table->dropColumn('carry_forward_timeframe_value');
            $table->unsignedTinyInteger('carry_forward_expiry_month')->after('carry_forward_limit')->nullable();
        });
    }
};
