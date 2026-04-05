<?php

namespace App\Models;

class Reservation extends User
{
    protected $table = 'reservations';

    public function room()
    {
        return $this->belongsTo(HotelRoom::class);
    }
}
