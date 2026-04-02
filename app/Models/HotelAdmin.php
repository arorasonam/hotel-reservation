<?php

namespace App\Models;

class HotelAdmin extends User
{
    protected $table = 'users';

    public function hotelGroup()
    {
        return $this->belongsTo(HotelGroup::class);
    }
}
