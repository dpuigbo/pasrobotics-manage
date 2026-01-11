<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Solo añadimos la columna si no existe (porque tu DB ya la tiene por el fallo anterior)
        if (! Schema::hasColumn('interventions', 'client_id')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
            });
        }

        // IMPORTANTE: no creamos FK todavía (te estaba rompiendo el deploy)
        // Cuando confirmemos el tipo exacto de clients.id, creamos otra migración SOLO para la FK.
    }

    public function down(): void
    {
        if (Schema::hasColumn('interventions', 'client_id')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->dropColumn('client_id');
            });
        }
    }
};
