<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intervention_systems', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['intervention_id', 'system_id'], 'interv_sys_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_systems');
    }
};
