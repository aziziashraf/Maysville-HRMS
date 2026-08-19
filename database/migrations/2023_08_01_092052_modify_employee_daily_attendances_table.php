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
            $table->text('remote_working_duration')->nullable()->after('duration')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_daily_attendances', function (Blueprint $table) {
            $table->string('remote_working_duration')->nullable()->after('duration')->change();
        });
    }
};
