<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeavesColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('confirmed_date')->nullable();
            $table->date('resigned_date')->nullable();
            $table->integer('medical_leave_balance')->nullable();
            $table->integer('annual_leave_balance')->nullable();
            $table->integer('forwarded_annual_leave_balance')->nullable();
            $table->integer('permission_leave_balance')->nullable();
            $table->integer('unpaid_leave_balance')->nullable();
            $table->boolean('leave_review')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('confirmed_date');
            $table->dropColumn('resigned_date');
            $table->dropColumn('medical_leave_balance');
            $table->dropColumn('annual_leave_balance');
            $table->dropColumn('forwarded_annual_leave_balance');
            $table->dropColumn('permission_leave_balance');
            $table->dropColumn('unpaid_leave_balance');
            $table->dropColumn('leave_review');
        });
    }

}
