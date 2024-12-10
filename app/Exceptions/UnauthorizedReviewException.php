<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedReviewException extends Exception
{
    /** The error message */
    protected $message = "You cannot post a review for an event you have not purchased a ticket for.";
}
