<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();

            $table->string('status')->default('draft'); // draft | signed | etc

            $table->dateTime('performed_start_at')->nullable();
            $table->dateTime('performed_end_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['intervention_id', 'system_id'], 'report_intervention_system_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
