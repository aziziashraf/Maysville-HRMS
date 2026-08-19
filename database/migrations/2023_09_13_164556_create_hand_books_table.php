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
        Schema::create('hand_books', function (Blueprint $table) {
            $table->id();
            $table->integer('handbookcategory_id')->nullable();
            $table->string('name');
            $table->string('description',255)->nullable();
            $table->boolean('status');
            $table->text('content');
            $table->char('index_number');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hand_books');
    }
};
