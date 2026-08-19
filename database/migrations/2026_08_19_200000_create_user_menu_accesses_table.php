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
        Schema::create('user_menu_accesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            // Matches a key in config/menu_access.php.
            $table->string('menu_key');
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            // One decision per staff member per menu item. Absence of a row
            // means "fall back to the item's configured default", so existing
            // accounts are unaffected until someone saves a choice for them.
            $table->unique(['user_id', 'menu_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_menu_accesses');
    }
};
