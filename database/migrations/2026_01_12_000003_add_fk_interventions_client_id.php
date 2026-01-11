<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            // Asegura que el tipo coincide con clients.id
            // (si ya existe, no la recreamos)
            if (! Schema::hasColumn('interventions', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
            }

            // Si la FK ya existiera, esto fallaría; pero ahora mismo no existe
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
    }
};
