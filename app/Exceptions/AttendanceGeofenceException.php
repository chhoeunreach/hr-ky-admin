<?php

namespace App\Exceptions;

use Exception;

class AttendanceGeofenceException extends Exception
{
    protected ?string $errorCode;
    protected ?array $errorData;

    public function __construct(
        string $message = '',
        int $code = 403,
        ?string $errorCode = 'OUT_OF_GEOFENCE',
        ?array $errorData = null
    ) {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
        $this->errorData = $errorData;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getErrorData(): ?array
    {
        return $this->errorData;
    }
}
