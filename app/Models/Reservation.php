<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'guest_id', // Add this line
        'room_type_id',
        'check_in',
        'check_out',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'rate',
        'nights',
        // ... any other fields you are saving
    ];

    public function room()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function guest()
    {
        return $this->belongsTo(User::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
