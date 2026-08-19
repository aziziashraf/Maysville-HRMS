<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLiftAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_lift_accesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('content_id');
            $table->string('content_type', 255);
            $table->integer('lift_access_id')->nullable();
            $table->string('role', 255)->nullable();
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
        Schema::dropIfExists('user_lift_accesses');
    }
}
