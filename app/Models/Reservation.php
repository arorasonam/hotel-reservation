<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'hotel_id',
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
        'room_no'
        // ... any other fields you are saving
    ];

    protected static function booted()
    {
        static::creating(function ($reservation) {
            if (!$reservation->reservation_number) {
                // 1. Get the Hotel Prefix (e.g., THE)
                $hotelPrefix = 'RES';
                if ($reservation->hotel) {
                    $hotelPrefix = strtoupper(substr($reservation->hotel->name, 0, 3));
                }

                // 2. Count how many reservations already exist for THIS hotel prefix
                // This ensures the first one is always 1, the second is 2, etc.
                $count = self::where('reservation_number', 'like', $hotelPrefix . '_%')->count();
                $nextId = $count + 1;

                // 3. Generate the formatted string: THE_0000001
                $reservation->reservation_number = $hotelPrefix . '_' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
            }
        });
    }

    public function room()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function guests()
    {
        return $this->belongsToMany(
            Guest::class,
            'reservation_guests',
            'reservation_id',
            'guest_id'
        );
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservationGuests()
    {
        return $this->hasMany(ReservationGuest::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function posOrders()
    {
        return $this->hasMany(PosOrder::class);
    }

    public function getTotalPosChargesAttribute()
    {
        return $this->posOrders()
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
    }
}
