<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('site')->nullable();

            // tarifas / extras (lo que ya tenías)
            $table->decimal('travel_hours', 8, 2)->nullable();
            $table->decimal('travel_days', 8, 2)->nullable();
            $table->integer('km')->nullable();
            $table->decimal('tolls', 10, 2)->nullable();
            $table->decimal('work_hour_rate', 10, 2)->nullable();
            $table->decimal('travel_hour_rate', 10, 2)->nullable();
            $table->decimal('diet_rate', 10, 2)->nullable();
            $table->decimal('access_mgmt_fee', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients'); 
    }
};
