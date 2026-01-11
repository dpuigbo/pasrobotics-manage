<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('visits')) {
            Schema::create('visits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable(); // luego lo conectamos a clients
                $table->string('type');   // preventive | corrective
                $table->string('status')->default('draft'); // draft | finalized | delivered
                $table->string('reference')->nullable();
                $table->string('title')->nullable();
                $table->dateTime('performed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['client_id', 'type', 'status']);
            });
        }

        if (!Schema::hasColumn('interventions', 'visit_id')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->foreignId('visit_id')->nullable()
                    ->constrained('visits')
                    ->nullOnDelete();

                $table->index('visit_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('interventions', 'visit_id')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('visit_id');
            });
        }

        Schema::dropIfExists('visits');
    }
};
