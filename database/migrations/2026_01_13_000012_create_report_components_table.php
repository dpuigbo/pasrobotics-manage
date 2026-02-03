<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->string('component_type');  // controller | mechanical_unit | drive_unit
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);

            // Foreign keys to system_components (nullable, one will be filled based on component_type)
            $table->foreignId('system_controller_unit_id')->nullable()->constrained('system_components')->nullOnDelete();
            $table->foreignId('system_mechanical_unit_id')->nullable()->constrained('system_components')->nullOnDelete();
            $table->foreignId('system_drive_unit_id')->nullable()->constrained('system_components')->nullOnDelete();

            // Template version reference
            $table->foreignId('template_version_id')->constrained('component_model_template_versions')->cascadeOnDelete();

            // Schema snapshot and data storage
            $table->json('schema_json')->nullable();
            $table->json('data_json')->nullable();

            $table->timestamps();

            // Index for sorting components within a report
            $table->index(['report_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_components');
    }
};
