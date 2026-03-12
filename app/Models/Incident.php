<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'title',
        'status',
        'postmortem',
        'resolved_at',
    ];

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** @return BelongsToMany<Component, $this> */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'incident_component');
    }

    /** @return HasMany<IncidentUpdate, $this> */
    public function updates(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class);
    }

    /** @param Builder<Incident> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', '!=', 'resolved');
    }

    /** @param Builder<Incident> $query */
    public function scopeResolved(Builder $query): void
    {
        $query->where('status', 'resolved');
    }

    public function isResolved(): bool
    {
        return $this->status === IncidentStatus::Resolved;
    }

    public function latestUpdate(): ?IncidentUpdate
    {
        return $this->updates()->orderBy('created_at', 'desc')->first();
    }

    protected function casts(): array
    {
        return [
            'status' => IncidentStatus::class,
            'resolved_at' => 'datetime',
        ];
    }
}
