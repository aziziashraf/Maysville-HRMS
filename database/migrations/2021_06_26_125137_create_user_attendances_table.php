<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->datetime('scan_datetime')->nullable();
            $table->date('scan_date')->nullable();
            $table->time('scan_time')->nullable();
            $table->integer('access_id')->nullable();
            $table->string('qr_access')->nullable();
            $table->string('remarks',255)->nullable();
            $table->string('status')->nullable();
            $table->string('scan_status')->nullable();
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
        Schema::dropIfExists('user_attendances');
    }
}
