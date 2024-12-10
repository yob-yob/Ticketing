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

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
}
