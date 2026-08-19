<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('nric')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->string('contact_no')->nullable();
            $table->integer('role_id')->nullable(); // by user access
            $table->string('role')->nullable(); // visitor,tenant,employee
            $table->string('staff_id')->nullable();
            $table->string('card_id')->nullable();
            $table->integer('visitor_pass_id')->nullable();
            $table->string('qr_access')->nullable();
            $table->datetime('qr_expired_datetime')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('position')->nullable();
            $table->double('salary', 8, 2)->nullable();
            $table->double('ot_working_hour', 8, 2)->nullable();
            $table->double('ot_weekend', 8, 2)->nullable();
            $table->double('ot_public_holiday', 8, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_active')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
