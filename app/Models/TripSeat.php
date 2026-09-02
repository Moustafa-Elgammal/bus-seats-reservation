<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripSeat extends Model
{
    use HasFactory;

    protected $table = 'trips_seats';

    /** seat reservations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'seat_id', 'id');
    }
}
