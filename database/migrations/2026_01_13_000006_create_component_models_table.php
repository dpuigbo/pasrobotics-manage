<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('component_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturer_id')->constrained('manufacturers');

            // controller | mechanical_unit | drive_unit (puedes ampliar)
            $table->string('type', 40);

            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['manufacturer_id', 'type']);
            $table->unique(['manufacturer_id', 'type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_models');
    }
};
