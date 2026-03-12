<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_daily_uptimes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('uptime_percentage', 5, 2);
            $table->unsignedInteger('minutes_operational');
            $table->unsignedInteger('minutes_excluded_for_maintenance')->default(0);
            $table->timestamps();

            $table->unique(['component_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_daily_uptimes');
    }
};
