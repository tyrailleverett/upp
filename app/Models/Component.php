<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'description',
        'group',
        'status',
        'sort_order',
    ];

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** @return HasMany<ComponentStatusLog, $this> */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ComponentStatusLog::class);
    }

    /** @return HasMany<ComponentDailyUptime, $this> */
    public function dailyUptimes(): HasMany
    {
        return $this->hasMany(ComponentDailyUptime::class);
    }

    /** @param Builder<Component> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /** @param Builder<Component> $query */
    public function scopeInGroup(Builder $query, string $group): void
    {
        $query->where('group', $group);
    }

    public function logStatusChange(): void
    {
        $this->statusLogs()->create([
            'status' => $this->status,
        ]);
    }

    protected function casts(): array
    {
        return [
            'status' => ComponentStatus::class,
            'sort_order' => 'integer',
        ];
    }
}
