<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationFolio extends Model
{
    protected $fillable = [

        'reservation_id',
        'source',
        'source_id',
        'description',
        'amount',
        'type',
        'posted_at'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
