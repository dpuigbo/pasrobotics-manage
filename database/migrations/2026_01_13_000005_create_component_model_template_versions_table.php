<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('component_model_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_model_id')->constrained('component_models')->cascadeOnDelete();

            // versión incremental por modelo (1,2,3...)
            $table->unsignedInteger('version');

            // esquema del formulario (JSON)
            $table->json('schema');

            $table->timestamps();

            // NOMBRE CORTO para evitar el error de "identifier too long"
            $table->unique(['component_model_id', 'version'], 'cm_tv_model_version_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_model_template_versions');
    }
};
