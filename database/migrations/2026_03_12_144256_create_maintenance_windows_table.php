<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('ends_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'scheduled_at']);
            $table->index(['ends_at', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_windows');
    }
};
