<?php

namespace App\Models;

use App\Services\ReservationFolioService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'room_no',
        // ... any other fields you are saving
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'rate' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($reservation) {
            if (! $reservation->reservation_number) {
                // 1. Get the Hotel Prefix (e.g., THE)
                $hotelPrefix = 'RES';
                if ($reservation->hotel) {
                    $hotelPrefix = strtoupper(substr($reservation->hotel->name, 0, 3));
                }

                // 2. Count how many reservations already exist for THIS hotel prefix
                // This ensures the first one is always 1, the second is 2, etc.
                $count = self::where('reservation_number', 'like', $hotelPrefix.'_%')->count();
                $nextId = $count + 1;

                // 3. Generate the formatted string: THE_0000001
                $reservation->reservation_number = $hotelPrefix.'_'.str_pad($nextId, 7, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function (Reservation $reservation): void {
            app(ReservationFolioService::class)->syncReservationStayCharge($reservation);
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(
            Guest::class,
            'reservation_guests',
            'reservation_id',
            'guest_id'
        );
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservationGuests(): HasMany
    {
        return $this->hasMany(ReservationGuest::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function getTotalPosChargesAttribute(): float
    {
        return (float) $this->posOrders()
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
    }

    public function folios(): HasMany
    {
        return $this->hasMany(ReservationFolio::class);
    }

    public function getTotalFolioAmountAttribute(): float
    {
        return (float) $this->folios()->sum('amount');
    }

    public function getTotalFolioDebitsAttribute(): float
    {
        return (float) $this->folios()
            ->where('type', 'debit')
            ->sum('amount');
    }

    public function getTotalFolioCreditsAttribute(): float
    {
        return (float) $this->folios()
            ->where('type', 'credit')
            ->sum('amount');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return round($this->total_folio_debits - $this->total_folio_credits, 2);
    }
}
