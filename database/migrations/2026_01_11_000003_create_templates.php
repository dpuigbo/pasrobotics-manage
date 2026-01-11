<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // "ABB IRB2600 - Unidad mecánica"
                $table->string('component_type'); // system_base, controller, mechanical_unit, drive_unit, corrective
                $table->string('manufacturer')->nullable(); // ABB, KUKA...
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('template_versions')) {
            Schema::create('template_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('templates')->cascadeOnDelete();
                $table->string('version'); // v1, v2...
                $table->boolean('is_published')->default(false);
                $table->json('schema_json'); // secciones/campos/tablas
                $table->timestamps();

                $table->unique(['template_id', 'version']);
            });
        }

        if (!Schema::hasTable('template_assignments')) {
            Schema::create('template_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('component_type'); // controller/mechanical_unit/drive_unit/system_base
                $table->foreignId('template_version_id')->constrained('template_versions')->cascadeOnDelete();

                $table->foreignId('robot_model_id')->nullable()->constrained('robot_models')->nullOnDelete();
                $table->foreignId('controller_model_id')->nullable()->constrained('controller_models')->nullOnDelete();
                $table->foreignId('drive_unit_model_id')->nullable()->constrained('drive_unit_models')->nullOnDelete();

                $table->unsignedSmallInteger('priority')->default(100); // menor = más específico
                $table->timestamps();

                $table->index(['component_type', 'priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_assignments');
        Schema::dropIfExists('template_versions');
        Schema::dropIfExists('templates');
    }
};
