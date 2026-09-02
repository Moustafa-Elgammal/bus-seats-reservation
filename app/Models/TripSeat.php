<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripSeat extends Model
{
    use HasFactory;

    protected $table = 'trips_seats';

    protected $fillable = ['trip_id', 'seat_no'];

    /** seat reservations
     * @return HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'seat_id', 'id');
    }
}
