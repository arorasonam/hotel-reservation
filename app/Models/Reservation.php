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
        'booking_source_id',
        'booking_type_id',
        'source_market_id',
        'reservation_number',
        'breakfast',
        'type',
        'rate_plan',
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
                if ($reservation->hotel_id) {
                    $hotel = \App\Models\Hotel::find($reservation->hotel_id);
                    $hotelPrefix = $hotel ? strtoupper(substr($hotel->name, 0, 3)) : 'RES';
                }

                /** * 2. Robust ID Generation
                 * Instead of count(), we look for the highest existing number to avoid 
                 * duplicate IDs if a previous reservation was deleted.
                 */
                $lastReservation = self::where('reservation_number', 'like', $hotelPrefix . '_%')
                    ->orderBy('reservation_number', 'desc')
                    ->first();

                if ($lastReservation) {
                    // Extract number from "THE_0000005" -> 5
                    $lastNumber = (int) str_replace($hotelPrefix . '_', '', $lastReservation->reservation_number);
                    $nextId = $lastNumber + 1;
                } else {
                    $nextId = 1;
                }

                // 3. Generate the formatted string: THE_0000001
                $reservation->reservation_number = $hotelPrefix . '_' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function (Reservation $reservation): void {
            if (! $reservation->roomCategories()->exists()) { // GP check if need to update for roomdetail
                app(ReservationFolioService::class)->syncReservationStayCharge($reservation);
            }
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

    public function room_requirements()
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function bookingSource()
    {
        return $this->belongsTo(BookingSource::class, 'booking_source_id');
    }

    public function bookingType()
    {
        return $this->belongsTo(BookingType::class, 'booking_type_id');
    }

    public function sourceMarket()
    {
        return $this->belongsTo(SourceMarket::class, 'source_market_id');
    }

    public function roomCategories(): HasMany
    {
        return $this->hasMany(ReservationRoomCategory::class);
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

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function isCheckedIn(): bool
    {
        return strtolower((string) $this->status) === 'checked_in';
    }
}
