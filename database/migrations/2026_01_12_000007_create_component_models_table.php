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
            $table->enum('type', ['robot', 'controller', 'drive_unit']);
            $table->string('model'); // IRB2600, IRC5 Single, KRC4...
            $table->string('variant')->nullable(); // opcional: IRC5 Single Cabinet, etc

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['manufacturer', 'type', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_models');
    }
};
