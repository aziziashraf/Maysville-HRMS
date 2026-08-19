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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('balance_unit')->after('description');
            $table->string('renew_freq')->after('balance_unit');
            $table->integer('default_amount')->after('renew_freq')->nullable();
            $table->boolean('carry_forward')->after('default_amount')->default(false);
            $table->integer('carry_forward_limit')->after('carry_forward')->nullable();
            $table->date('carry_forward_expiry_date')->after('carry_forward_limit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('balance_unit');
            $table->dropColumn('renew_freq');
            $table->dropColumn('default_amount');
            $table->dropColumn('carry_forward');
            $table->dropColumn('carry_forward_limit');
            $table->dropColumn('carry_forward_expiry_date');
        });
    }
};
