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
            $table->unsignedInteger('version');  // 1,2,3...
            $table->longText('schema')->nullable(); // JSON / texto
            $table->timestamps();

            $table->unique(['component_model_id', 'version'], 'cm_tv_cm_ver_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_model_template_versions');
    }
};
