<?php

namespace App\Exceptions;

use Exception;

class EventBookingClosedException extends Exception
{
    protected $message = "The booking deadline for this event has passed. No further bookings are allowed.";
}
