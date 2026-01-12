<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->foreignId('plant_id')->nullable()->after('client_id')->constrained('plants')->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->after('plant_id')->constrained('machines')->nullOnDelete();

            $table->index(['client_id', 'plant_id', 'machine_id']);
        });
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machine_id');
            $table->dropConstrainedForeignId('plant_id');
        });
    }
};
