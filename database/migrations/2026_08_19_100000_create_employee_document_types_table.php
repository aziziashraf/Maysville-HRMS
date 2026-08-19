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
        Schema::create('employee_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 255)->nullable();

            // Behaviour of records belonging to this type. Admin-configurable so a
            // new kind of employee information can be added without a code change.
            $table->boolean('has_expiry')->default(true);
            $table->unsignedSmallInteger('expiry_warning_days')->default(30);
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Extra labelled fields defined by the admin, as an array of
            // { key, label, type, required }. Lets a type capture information we
            // have not modelled as a real column.
            $table->json('custom_fields')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_document_types');
    }
};
