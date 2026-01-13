<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('component_models', function (Blueprint $table) {
            $table->id();

            // ABB / KUKA / FANUC... (lista controlada en UI)
            $table->string('manufacturer');

            // robot | controller | drive_unit
            $table->string('type');

            // IRB2600, IRC5 Single Cabinet, KRC4, etc.
            $table->string('name');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['manufacturer', 'type']);
            $table->unique(['manufacturer', 'type', 'name'], 'cm_man_type_name_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_models');
    }
};
