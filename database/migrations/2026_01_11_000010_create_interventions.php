<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();

            $table->string('type');   // preventive | corrective
            $table->string('status')->default('draft'); // draft | finalized | delivered
            $table->string('reference')->nullable(); // opcional: numeración interna
            $table->string('title')->nullable();

            $table->dateTime('performed_at')->nullable(); // fecha intervención
            $table->text('notes')->nullable(); // observaciones generales

            $table->timestamps();

            $table->index(['system_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
