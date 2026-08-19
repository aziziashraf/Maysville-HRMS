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
        Schema::table('leave_balance_logs', function (Blueprint $table) {
            $table->decimal('balance_before', 5, 1)->change();
            $table->decimal('balance_after', 5, 1)->change();
            $table->decimal('carry_forward_balance_before', 5, 1)->change();
            $table->decimal('carry_forward_balance_after', 5, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balance_logs', function (Blueprint $table) {
            $table->integer('balance_before')->change();
            $table->integer('balance_after')->change();
            $table->integer('carry_forward_balance_before')->change();
            $table->integer('carry_forward_balance_after')->change();
        });
    }
};
