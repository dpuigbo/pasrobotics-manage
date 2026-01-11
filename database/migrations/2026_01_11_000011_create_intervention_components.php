<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intervention_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();

            $table->string('component_type'); // system_base | controller | mechanical_unit | drive_unit
            $table->string('label')->nullable(); // snapshot label (ROB_1, etc.)
            $table->unsignedInteger('sort_order')->default(0);

            // Referencia al componente del sistema (uno de estos)
            $table->foreignId('system_controller_unit_id')->nullable()->constrained('system_controller_units')->nullOnDelete();
            $table->foreignId('system_mechanical_unit_id')->nullable()->constrained('system_mechanical_units')->nullOnDelete();
            $table->foreignId('system_drive_unit_id')->nullable()->constrained('system_drive_units')->nullOnDelete();

            // Plantilla usada + snapshot del schema
            $table->foreignId('template_version_id')->constrained('template_versions');
            $table->json('schema_json'); // snapshot de template_versions.schema_json
            $table->json('data_json')->nullable(); // respuestas

            $table->timestamps();

            $table->index(['intervention_id', 'component_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_components');
    }
};
