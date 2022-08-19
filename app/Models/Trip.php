<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    public function stations(){
        return $this->hasMany(TripsStation::class, 'trip_id', 'id');
    }

    public function seats()
    {
        return $this->hasMany(TripsSeat::class,'trip_id','id');
    }
}
