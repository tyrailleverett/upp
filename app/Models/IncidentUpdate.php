<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IncidentUpdate extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'incident_id',
        'status',
        'message',
    ];

    /** @return BelongsTo<Incident, $this> */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    protected function casts(): array
    {
        return [
            'status' => IncidentStatus::class,
        ];
    }
}
