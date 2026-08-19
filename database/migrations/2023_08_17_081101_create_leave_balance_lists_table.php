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
        Schema::create('leave_balance_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_balance_id');
            $table->decimal('balance', 5, 1);
            $table->date('expiry_date')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_lists');
    }
};
