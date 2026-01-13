<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->string('type', 40);     // preventive/corrective
            $table->string('status', 40)->default('draft');

            $table->string('reference', 50)->nullable();
            $table->string('title', 150)->nullable();

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
