<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Exceptions\CannotReviewOngoingEventException;
use App\Exceptions\EventBookingClosedException;
use App\Exceptions\InsufficientTicketsException;
use App\Exceptions\UnauthorizedReviewException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function reviewEvent(Event $event, string $comment, int $rating)
    {
        // I expect an event to atleast have a duration of 1 hour...
        throw_if($event->datetime->addHour()->isFuture(), CannotReviewOngoingEventException::class);

        $event_ids = $this->tickets()->pluck('event_id');

        throw_unless(in_array($event->id, $event_ids->toArray()), UnauthorizedReviewException::class);

        $review = $this->reviews()->create([
            'event_id' => $event->id,
            'comment' => $comment,
            'rating' => $rating,
        ]);

        return $review;
    }

    public function reserveTickets(Event $event, $number_of_tickets)
    {
        throw_if($event->datetime->isPast(), EventBookingClosedException::class);

        $tickets = $event->availableTickets()->limit($number_of_tickets)->get();
        
        throw_if($tickets->count() < $number_of_tickets, InsufficientTicketsException::class);

        Ticket::query()->whereIn('id', $tickets->pluck('id'))->update([
            'user_id' => $this->id
        ]);

        return $tickets;
    }
}
