<?php

namespace App\Exceptions;

use Exception;

class CannotReviewOngoingEventException extends Exception
{
    /** The error message */
    protected $message = "Please wait until the event concludes before submitting a review.";
}
