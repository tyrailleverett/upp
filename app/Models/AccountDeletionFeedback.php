<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeletionReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AccountDeletionFeedback extends Model
{
    /** @use HasFactory<\Database\Factories\AccountDeletionFeedbackFactory> */
    use HasFactory;

    protected $table = 'account_deletion_feedback';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'reason',
        'comment',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reason' => DeletionReason::class,
        ];
    }
}
