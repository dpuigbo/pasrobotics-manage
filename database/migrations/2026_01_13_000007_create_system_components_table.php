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

            // controller | mechanical_unit | drive_unit
            $table->string('role');

            // Etiqueta editable: "ROB_1", "ROB_2", "Cabinet", "DU_1"...
            $table->string('label')->nullable();

            $table->string('serial_number')->nullable();
            $table->date('manufactured_at')->nullable();
            $table->unsignedSmallInteger('axes_count')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['system_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_components');
    }
};
