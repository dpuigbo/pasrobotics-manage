<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('robot_models')) {
            Schema::create('robot_models', function (Blueprint $table) {
                $table->id();
                $table->string('manufacturer'); // ABB, KUKA, etc.
                $table->string('name');         // IRB2600-12/1.65, KR120 R2500...
                $table->string('family')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['manufacturer', 'name']);
            });
        }

        if (!Schema::hasTable('controller_models')) {
            Schema::create('controller_models', function (Blueprint $table) {
                $table->id();
                $table->string('manufacturer');
                $table->string('name'); // IRC5 Single, IRC5 Compact, KRC4...
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['manufacturer', 'name']);
            });
        }

        if (!Schema::hasTable('drive_unit_models')) {
            Schema::create('drive_unit_models', function (Blueprint $table) {
                $table->id();
                $table->string('manufacturer');
                $table->string('name'); // IRC5 Drive Unit, etc.
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['manufacturer', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_unit_models');
        Schema::dropIfExists('controller_models');
        Schema::dropIfExists('robot_models');
    }
};
