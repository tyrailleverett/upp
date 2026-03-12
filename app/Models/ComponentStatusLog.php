<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ComponentStatusLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'component_id',
        'status',
    ];

    /** @return BelongsTo<Component, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ComponentStatus::class,
        ];
    }
}
