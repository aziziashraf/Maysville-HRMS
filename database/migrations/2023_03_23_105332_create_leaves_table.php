<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('leave_type_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('remarks',255)->nullable();
            $table->boolean('review_status')->nullable();
            $table->integer('reviewed_by')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->string('review_remark',255)->nullable();
            $table->boolean('approval_status')->nullable();
            $table->integer('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->string('approval_remark',255)->nullable();
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
        Schema::dropIfExists('leaves');
    }
}
