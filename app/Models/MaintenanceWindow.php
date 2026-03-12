<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class MaintenanceWindow extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'title',
        'description',
        'scheduled_at',
        'ends_at',
        'completed_at',
        'started_notified_at',
    ];

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** @return BelongsToMany<Component, $this> */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'maintenance_component');
    }

    /** @param Builder<MaintenanceWindow> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('scheduled_at', '<=', now())
            ->where('ends_at', '>', now())
            ->whereNull('completed_at');
    }

    /** @param Builder<MaintenanceWindow> $query */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('scheduled_at', '>', now())
            ->whereNull('completed_at');
    }

    /** @param Builder<MaintenanceWindow> $query */
    public function scopeCompleted(Builder $query): void
    {
        $query->whereNotNull('completed_at');
    }

    /** @param Builder<MaintenanceWindow> $query */
    public function scopeExpired(Builder $query): void
    {
        $query->where('ends_at', '<=', now())
            ->whereNull('completed_at');
    }

    public function isActive(): bool
    {
        return $this->scheduled_at <= now()
            && $this->ends_at > now()
            && $this->completed_at === null;
    }

    public function isUpcoming(): bool
    {
        return $this->scheduled_at > now()
            && $this->completed_at === null;
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
            'started_notified_at' => 'datetime',
        ];
    }
}
