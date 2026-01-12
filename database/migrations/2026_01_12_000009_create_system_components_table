<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->foreignId('component_model_id')->constrained('component_models')->restrictOnDelete();

            $table->string('label')->nullable(); // editable: “Robot 1”, “MU1”, etc
            $table->string('serial')->nullable();

            // Para ABB multimove: “main_controller”, “drive_unit”, etc.
            $table->string('role')->nullable();

            $table->unsignedInteger('position')->default(1); // orden dentro del sistema
            $table->json('meta')->nullable(); // libre (ejes, payload, etc)

            $table->timestamps();

            $table->index(['system_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_components');
    }
};
