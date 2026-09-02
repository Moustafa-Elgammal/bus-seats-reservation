<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStation extends Model
{
    use HasFactory;

    protected $table = 'trips_stations';

    protected $fillable = ['trip_id', 'city_id', 'station_order'];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
