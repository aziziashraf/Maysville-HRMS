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
        Schema::create('leave_deduction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_balance_list_id');
            $table->unsignedBigInteger('leave_id');
            $table->decimal('amount', 5, 1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_deduction_logs');
    }
};
