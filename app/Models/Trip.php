<?php

namespace App\Models;

use App\Observers\TripObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(TripObserver::class)]
class Trip extends Model
{
    use HasFactory;

    public function stations()
    {
        return $this->hasMany(TripsStation::class, 'trip_id', 'id');
    }

    public function seats()
    {
        return $this->hasMany(TripsSeat::class, 'trip_id', 'id');
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id', 'id');
    }
}
