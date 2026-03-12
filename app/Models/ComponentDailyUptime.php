<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ComponentDailyUptime extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'date',
        'uptime_percentage',
        'minutes_operational',
        'minutes_excluded_for_maintenance',
    ];

    /** @return BelongsTo<Component, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'uptime_percentage' => 'decimal:2',
            'minutes_operational' => 'integer',
            'minutes_excluded_for_maintenance' => 'integer',
        ];
    }
}
