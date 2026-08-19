<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDailyAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->date('check_in_date')->nullable();
            $table->string('name')->nullable();
            $table->string('staff_id')->nullable();
            $table->string('department_name')->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('duration')->nullable();
            $table->string('location')->nullable();
            $table->string('level')->nullable();
            $table->string('status')->nullable();
            $table->string('remarks')->nullable();
            $table->string('timestamp')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_daily_attendances');
    }
}
