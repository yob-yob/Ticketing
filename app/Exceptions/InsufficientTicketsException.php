<?php

namespace App\Exceptions;

use Exception;

class InsufficientTicketsException extends Exception
{
    protected $message = "The requested number of tickets exceeds the available quantity.";
}
