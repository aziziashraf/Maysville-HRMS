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
            $table->dropColumn('carry_forward_expiry_date');
            $table->unsignedTinyInteger('carry_forward_expiry_month')->after('carry_forward_limit')->nullable();
            $table->unsignedBigInteger('leave_type_to_deduct_if_depleted')->after('carry_forward_expiry_month')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('carry_forward_expiry_month');
            $table->date('carry_forward_expiry_date')->after('carry_forward_limit')->nullable();
            $table->dropColumn('leave_type_to_deduct_if_depleted');
        });
    }
};
