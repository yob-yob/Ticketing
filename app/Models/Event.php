<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    public $fillable = [
        'title',
        'description',
        'datetime',
        'location',
        'price',
    ];

    public function casts() {
        return [
            'datetime' => 'datetime'
        ];
    }

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function availableTickets() {
        return $this->tickets()->whereNull('user_id');
    }

    public function reservedTickets() {
        return $this->tickets()->whereNotNull('user_id');
    }

    public function createTickets(int $count = 0) {
        for ($i = 0; $i < $count; $i++) { 
            $this->tickets()->create(); // can be optimized to use insert instead of creating one-by-one, but this will do for now.
        }
    }
}
