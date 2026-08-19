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
            $table->boolean('unlimited')->default(false)->nullable()->after('confirmed_employees_only');
            $table->boolean('limit_per_leave')->default(false)->nullable()->after('unlimited');
            $table->integer('limit_per_leave_amount')->default(0)->nullable()->after('limit_per_leave');
            $table->boolean('back_dated')->default(false)->nullable()->after('limit_per_leave_amount');
            $table->integer('back_dated_days_limit')->default(0)->nullable()->after('back_dated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('unlimited');
            $table->dropColumn('limit_per_leave');
            $table->dropColumn('limit_per_leave_amount');
            $table->dropColumn('back_dated');
            $table->dropColumn('back_dated_days_limit');
        });
    }
};
