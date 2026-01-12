<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('component_models', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer'); // ABB, KUKA, FANUC...
            $table->enum('type', ['mechanical_unit', 'controller', 'drive_unit']);
            $table->string('model_name'); // IRB2600, IRC5 Single, etc.
            $table->string('variant')->nullable(); // opcional: “Single Cabinet”, etc.
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['manufacturer', 'type', 'model_name'], 'cm_mfr_type_model_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_models');
    }
};
