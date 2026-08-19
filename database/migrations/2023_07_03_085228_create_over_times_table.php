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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->string('reasons',255)->nullable();
            $table->decimal('estimated_time_taken', 8, 2);
            $table->time('actual_time_start');
            $table->time('actual_time_end');
            $table->decimal('actual_time_taken', 8, 2);
            $table->decimal('actual_time_approved', 8, 2);
            $table->string('claim_as');
            $table->string('remarks',255)->nullable();
            $table->string('status')->default('draft');
            $table->boolean('pre_review_status')->nullable();
            $table->unsignedBigInteger('pre_reviewed_by')->nullable();
            $table->datetime('pre_reviewed_at')->nullable();
            $table->string('pre_review_remark',255)->nullable();
            $table->boolean('review_status')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->string('review_remark',255)->nullable();
            $table->boolean('approval_status')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->string('approval_remark',255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
