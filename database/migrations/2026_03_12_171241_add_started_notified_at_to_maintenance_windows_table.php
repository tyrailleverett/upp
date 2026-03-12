<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_windows', function (Blueprint $table): void {
            $table->timestamp('started_notified_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_windows', function (Blueprint $table): void {
            $table->dropColumn('started_notified_at');
        });
    }
};
