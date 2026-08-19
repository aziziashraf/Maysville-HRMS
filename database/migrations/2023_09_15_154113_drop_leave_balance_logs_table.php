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
        Schema::dropIfExists('leave_balance_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('leave_balance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_balance_id');
            $table->string('reason');
            $table->decimal('balance_before', 5, 1);
            $table->decimal('balance_after', 5, 1);
            $table->decimal('carry_forward_balance_before', 5, 1);
            $table->decimal('carry_forward_balance_after', 5, 1);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }
};
