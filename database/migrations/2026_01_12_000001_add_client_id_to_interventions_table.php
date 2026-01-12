<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('interventions', 'client_id')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
            });
        }
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
