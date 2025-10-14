<?php

namespace App\Exceptions;

use RuntimeException;

class CalendlyNotConfiguredException extends RuntimeException
{
    public function __construct(string $message = "Calendly is not configured")
    {
        parent::__construct($message);
    }
}
