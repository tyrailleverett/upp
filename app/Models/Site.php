<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentStatus;
use App\Enums\SiteVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'visibility',
        'custom_domain',
        'logo_path',
        'favicon_path',
        'accent_color',
        'custom_css',
        'meta_title',
        'meta_description',
        'published_at',
        'suspended_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Component, $this> */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function isPublished(): bool
    {
        return $this->visibility === SiteVisibility::Published;
    }

    public function isDraft(): bool
    {
        return $this->visibility === SiteVisibility::Draft;
    }

    public function isSuspended(): bool
    {
        return $this->visibility === SiteVisibility::Suspended;
    }

    public function overallStatus(): ComponentStatus
    {
        $components = $this->relationLoaded('components')
            ? $this->components
            : $this->components()->get(['status']);

        if ($components->isEmpty()) {
            return ComponentStatus::Operational;
        }

        return $components
            ->reduce(
                fn (ComponentStatus $worstStatus, Component $component) => $component->status->severity() > $worstStatus->severity()
                    ? $component->status
                    : $worstStatus,
                ComponentStatus::Operational,
            );
    }

    /** @param Builder<Site> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('visibility', SiteVisibility::Published->value);
    }

    /** @param Builder<Site> $query */
    public function scopeDraft(Builder $query): void
    {
        $query->where('visibility', SiteVisibility::Draft->value);
    }

    /** @param Builder<Site> $query */
    public function scopeSuspended(Builder $query): void
    {
        $query->where('visibility', SiteVisibility::Suspended->value);
    }

    /** @param Builder<Site> $query */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'visibility' => SiteVisibility::class,
            'published_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }
}
