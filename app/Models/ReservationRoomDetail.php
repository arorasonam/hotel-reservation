<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRoomDetail extends Model
{
    protected $table = 'reservation_room_details';
    protected $guarded = [];
    protected $fillable = [
        'category_id',
        'room_number',
        'adults',
        'children',
        'infants',
        'status',
    ];

    /**
     * Relationship back to the Category Summary
     */
    public function category()
    {
        return $this->belongsTo(ReservationRoomCategory::class, 'category_id');
    }
}
