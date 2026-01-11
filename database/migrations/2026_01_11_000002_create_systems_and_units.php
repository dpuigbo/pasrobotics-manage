<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('systems')) {
            Schema::create('systems', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable(); // si tienes tabla clients
                $table->string('name'); // L4000TMP R7, WJ12, etc.
                $table->string('manufacturer')->nullable(); // ABB/KUKA...
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('client_id');
            });
        }

        if (!Schema::hasTable('system_controller_units')) {
            Schema::create('system_controller_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
                $table->foreignId('controller_model_id')->constrained('controller_models');
                $table->string('serial_number')->nullable();
                $table->string('label')->nullable(); // "Cabinet", "KRC4", etc.
                $table->date('manufactured_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique('system_id'); // 1 controladora por sistema
            });
        }

        if (!Schema::hasTable('system_mechanical_units')) {
            Schema::create('system_mechanical_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
                $table->foreignId('robot_model_id')->constrained('robot_models');
                $table->string('label')->nullable(); // ROB_1, ROB_2...
                $table->string('serial_number')->nullable();
                $table->date('manufactured_at')->nullable();
                $table->unsignedTinyInteger('axes_count')->nullable(); // 6 típico
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['system_id', 'robot_model_id']);
            });
        }

        if (!Schema::hasTable('system_drive_units')) {
            Schema::create('system_drive_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
                $table->foreignId('drive_unit_model_id')->constrained('drive_unit_models');
                $table->foreignId('system_mechanical_unit_id')->nullable()
                    ->constrained('system_mechanical_units')->nullOnDelete(); // opcional: asignar drive a un robot
                $table->string('label')->nullable(); // DU_1, DU_2...
                $table->string('serial_number')->nullable();
                $table->date('manufactured_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['system_id', 'system_mechanical_unit_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_drive_units');
        Schema::dropIfExists('system_mechanical_units');
        Schema::dropIfExists('system_controller_units');
        Schema::dropIfExists('systems');
    }
};
