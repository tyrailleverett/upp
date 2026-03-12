<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_window_id')->constrained('maintenance_windows')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();

            $table->unique(['maintenance_window_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_component');
    }
};
