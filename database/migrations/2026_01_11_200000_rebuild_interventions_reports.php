<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Viejo (si existe)
        Schema::dropIfExists('intervention_components'); // lo que montamos antes
        Schema::dropIfExists('report_components');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('intervention_systems');

        // OJO: antes "interventions" era informe. Lo destruimos y lo redefinimos.
        Schema::dropIfExists('interventions');

        Schema::enableForeignKeyConstraints();

        // Intervenciones (operación real)
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();

            $table->string('type')->default('preventive'); // o lo que uses
            $table->string('status')->default('draft');

            $table->string('reference')->nullable();
            $table->string('title')->nullable();

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'type', 'status']);
        });

        // Sistemas incluidos en la intervención (lista de trabajo)
        Schema::create('intervention_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();

            $table->timestamps();
            $table->unique(['intervention_id', 'system_id']);
        });

        // Informe por sistema dentro de la intervención
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();

            $table->string('status')->default('draft'); // draft | finalized | delivered

            $table->dateTime('performed_start_at')->nullable();
            $table->dateTime('performed_end_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->unique(['intervention_id', 'system_id']);
        });

        // Componentes internos del informe (NO se rellenan por separado)
        Schema::create('report_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();

            $table->string('component_type'); // controller | mechanical_unit | drive_unit | system_base (si lo usas)
            $table->string('label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('system_controller_unit_id')->nullable()->constrained('system_controller_units')->nullOnDelete();
            $table->foreignId('system_mechanical_unit_id')->nullable()->constrained('system_mechanical_units')->nullOnDelete();
            $table->foreignId('system_drive_unit_id')->nullable()->constrained('system_drive_units')->nullOnDelete();

            $table->foreignId('template_version_id')->nullable()->constrained('template_versions')->nullOnDelete();

            $table->json('schema_json');
            $table->json('data_json')->nullable();

            $table->timestamps();

            $table->index(['report_id', 'component_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_components');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('intervention_systems');
        Schema::dropIfExists('interventions');
    }
};
