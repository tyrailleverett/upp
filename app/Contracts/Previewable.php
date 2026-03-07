<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Mail\Mailable;

interface Previewable
{
    /**
     * Create a representative instance of this mailable for preview purposes.
     */
    public static function preview(): Mailable;
}
