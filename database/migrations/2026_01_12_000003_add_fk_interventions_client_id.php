<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Asegurar InnoDB (si fuese MyISAM, las FK fallan)
        DB::statement("ALTER TABLE clients ENGINE=InnoDB");
        DB::statement("ALTER TABLE interventions ENGINE=InnoDB");

        Schema::table('interventions', function (Blueprint $table) {
            // Si por lo que sea no existe la columna, la crea
            if (! Schema::hasColumn('interventions', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
            }

            // Crea FK (si ya existe, esto fallará; pero ahora mismo no existe)
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
    }
};
