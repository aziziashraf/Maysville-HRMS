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
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('balance', 5, 1)->default(0)->change();
            $table->decimal('carry_forward_balance', 5, 1)->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('balance')->default(0)->change();
            $table->integer('carry_forward_balance')->default(0)->nullable()->change();
        });
    }
};
