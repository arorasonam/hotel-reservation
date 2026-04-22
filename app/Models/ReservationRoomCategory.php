<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRoomCategory extends Model
{
    protected $table = 'reservation_room_categories';
    protected $guarded = [];
    protected $fillable = [
        'reservation_id',
        'room_type_id',
        'meal_plan_id',
        'rooms_count',
    ];

    public function roomDetails()
    {
        return $this->hasMany(ReservationRoomDetail::class, 'category_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
