<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'customers_seats_reservations';

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function seat()
    {
        return $this->belongsTo(TripSeat::class, 'seat_id', 'id');
    }
}
