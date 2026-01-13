<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();

            $table->string('manufacturer')->nullable();
            $table->string('name')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'manufacturer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};
