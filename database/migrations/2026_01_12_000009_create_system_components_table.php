<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('system_id')
                ->constrained('systems')
                ->cascadeOnDelete();

            $table->foreignId('component_model_id')
                ->constrained('component_models')
                ->restrictOnDelete();

            $table->enum('role', ['controller', 'mechanical_unit', 'drive_unit']);
            $table->string('label')->nullable(); // editable: "Robot 1"
            $table->string('serial_number')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->json('meta')->nullable(); // info técnica opcional
            $table->timestamps();

            $table->index(['system_id', 'role'], 'syscomp_sys_role_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_components');
    }
};
