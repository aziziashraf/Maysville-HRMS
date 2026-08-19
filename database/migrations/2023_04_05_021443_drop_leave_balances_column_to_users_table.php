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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('medical_leave_balance');
            $table->dropColumn('annual_leave_balance');
            $table->dropColumn('forwarded_annual_leave_balance');
            $table->dropColumn('permission_leave_balance');
            $table->dropColumn('unpaid_leave_balance');
            $table->integer('position_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('medical_leave_balance')->nullable();
            $table->integer('annual_leave_balance')->nullable();
            $table->integer('forwarded_annual_leave_balance')->nullable();
            $table->integer('permission_leave_balance')->nullable();
            $table->integer('unpaid_leave_balance')->nullable();
            $table->dropColumn('position_id');
        });
    }
};
