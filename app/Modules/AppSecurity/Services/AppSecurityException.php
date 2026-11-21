<?php

namespace App\Modules\AppSecurity\Services;

use RuntimeException;

class AppSecurityException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 403,
        public readonly ?string $securityEvent = null
    )
    {
        parent::__construct($message);
    }
}
