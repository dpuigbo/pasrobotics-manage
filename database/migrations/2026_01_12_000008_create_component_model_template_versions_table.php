<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('component_model_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_model_id')
                ->constrained('component_models')
                ->cascadeOnDelete();

            $table->unsignedInteger('version');
            $table->string('status')->default('draft'); // draft | active | deprecated
            $table->string('name')->nullable(); // “Informe IRB6700 v1”
            $table->json('schema')->nullable(); // builder del formulario
            $table->text('notes')->nullable();
            $table->timestamps();

            // índice único con nombre corto
            $table->unique(['component_model_id', 'version'], 'cmtv_model_ver_uq');
            $table->index(['component_model_id', 'status'], 'cmtv_model_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_model_template_versions');
    }
};
