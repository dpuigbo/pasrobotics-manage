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
            $table->foreignId('component_model_id')->constrained('component_models');

            // Redundancia útil para filtrar sin join (opcional)
            $table->string('type', 40);

            $table->string('label')->nullable();         // "Cabinet", "ROB_1", "DU_1"...
            $table->string('serial_number')->nullable();
            $table->date('manufactured_at')->nullable();
            $table->unsignedTinyInteger('axes_count')->nullable(); // solo si aplica

            $table->json('meta')->nullable();            // campos extra sin migrar cada vez
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['system_id', 'type']);
            $table->index(['component_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_components');
    }
};
