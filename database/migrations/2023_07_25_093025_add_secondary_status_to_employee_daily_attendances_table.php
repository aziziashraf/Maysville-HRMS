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
        Schema::table('employee_daily_attendances', function (Blueprint $table) {
            $table->string('secondary_status')->nullable()->after('status');
            $table->string('remote_working_duration')->nullable()->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_daily_attendances', function (Blueprint $table) {
            $table->dropColumn('secondary_status');
            $table->dropColumn('remote_working_duration');
        });
    }
};
