<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationGuest extends Model
{
    protected $table = 'reservation_guests';

    protected $fillable = [
        'reservation_id',
        'guest_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'is_primary',
    ];

    /**
     * Relationship to the User model (the guest)
     */
    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    /**
     * Relationship back to the Reservation
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
