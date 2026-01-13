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

            // preventive | corrective
            $table->string('type');

            // draft | in_progress | closed
            $table->string('status')->default('draft');

            $table->string('reference')->nullable();
            $table->string('title')->nullable();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
