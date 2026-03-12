<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('visibility')->default('draft');
            $table->string('custom_domain')->nullable()->unique();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('accent_color', 7)->nullable();
            $table->text('custom_css')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
