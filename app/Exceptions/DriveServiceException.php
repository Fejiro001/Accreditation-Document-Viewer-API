<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DriveServiceException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function toArray()
    {
        return [
            'error' => $this->getMessage(),
            'code' => $this->getCode(),
            'trace' => config('app.debug') ? $this->getTrace() : []
        ];
    }
}
