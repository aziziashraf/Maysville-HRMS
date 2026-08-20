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
        Schema::create('employee_resumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            $table->string('headline')->nullable();
            $table->text('summary')->nullable();

            // Repeating sections. Stored as JSON for the same reason the
            // employee information custom fields are: the shapes are simple,
            // only ever read as a whole for one employee, and never queried
            // across employees — a table each would buy nothing here.
            $table->json('education')->nullable();
            $table->json('experience')->nullable();
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();
            $table->json('references')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_resumes');
    }
};
