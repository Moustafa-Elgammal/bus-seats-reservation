<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStop extends Model
{
    use HasFactory;

    protected $table = 'reservations_stops';

    protected $fillable = ['reservation_id', 'city_id'];
}
