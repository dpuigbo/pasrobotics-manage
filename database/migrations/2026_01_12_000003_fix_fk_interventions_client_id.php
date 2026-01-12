<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function fkExists(): bool
    {
        $db = DB::scalar('SELECT DATABASE()');

        $name = DB::scalar("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'interventions'
              AND COLUMN_NAME = 'client_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db]);

        return ! empty($name);
    }

    public function up(): void
    {
        // Asegura InnoDB (si interventions fuera MyISAM, la FK falla con errno 150)
        DB::statement("ALTER TABLE `clients` ENGINE=InnoDB");
        DB::statement("ALTER TABLE `interventions` ENGINE=InnoDB");

        // Asegura columna y tipo correctos
        if (! Schema::hasColumn('interventions', 'client_id')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
            });
        } else {
            // Forzar tipo correcto (requiere doctrine/dbal, pero tú ya lo tienes instalado)
            Schema::table('interventions', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable()->change();
            });
        }

        // Crea FK solo si no existe
        if (! $this->fkExists()) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->foreign('client_id')
                    ->references('id')
                    ->on('clients')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Borrar FK si existe (por seguridad)
        $db = DB::scalar('SELECT DATABASE()');
        $name = DB::scalar("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'interventions'
              AND COLUMN_NAME = 'client_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db]);

        if ($name) {
            DB::statement("ALTER TABLE `interventions` DROP FOREIGN KEY `$name`");
        }
    }
};
