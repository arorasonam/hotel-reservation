<?php

namespace App\Models;

use App\Services\CurrencyService;
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
        'base_rate',
        'nights',
        'room_no',
        'booking_source_id',
        'booking_type_id',
        'source_market_id',
        'reservation_number',
        'breakfast',
        'type',
        'rate_plan',
        'currency_code',
        'exchange_rate_used',
        'base_price',
        'tax_amount',
        'base_tax_amount',
        'discount_amount',
        'base_discount_amount',
        'total_amount',
        'base_total_amount',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'rate' => 'decimal:2',
            'base_rate' => 'decimal:2',
            'exchange_rate_used' => 'decimal:6',
            'base_price' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'base_tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'base_discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'base_total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Reservation $reservation): void {
            $currencyCode = $reservation->currency_code ?: 'INR';
            $rate = (float) ($reservation->exchange_rate_used ?: app(CurrencyService::class)->getRate($currencyCode));
            $roomCharge = round((float) $reservation->rate * max(1, (int) $reservation->nights), 2);
            $taxAmount = (float) $reservation->tax_amount;
            $discountAmount = (float) $reservation->discount_amount;
            $totalAmount = round($roomCharge + $taxAmount - $discountAmount, 2);

            $reservation->currency_code = $currencyCode;
            $reservation->exchange_rate_used = $rate;
            $reservation->base_price = $roomCharge;
            $reservation->total_amount = $totalAmount;
            $reservation->base_rate = app(CurrencyService::class)->toBase((float) $reservation->rate, $rate);
            $reservation->base_tax_amount = app(CurrencyService::class)->toBase($taxAmount, $rate);
            $reservation->base_discount_amount = app(CurrencyService::class)->toBase($discountAmount, $rate);
            $reservation->base_total_amount = app(CurrencyService::class)->toBase($totalAmount, $rate);
        });

        static::creating(function ($reservation) {
            if (! $reservation->reservation_number) {
                // 1. Get the Hotel Prefix (e.g., THE)
                $hotelPrefix = 'RES';
                if ($reservation->hotel_id) {
                    $hotel = Hotel::find($reservation->hotel_id);
                    $hotelPrefix = $hotel ? strtoupper(substr($hotel->name, 0, 3)) : 'RES';
                }

                /** * 2. Robust ID Generation
                 * Instead of count(), we look for the highest existing number to avoid
                 * duplicate IDs if a previous reservation was deleted.
                 */
                $lastReservation = self::where('reservation_number', 'like', $hotelPrefix.'_%')
                    ->orderBy('reservation_number', 'desc')
                    ->first();

                if ($lastReservation) {
                    // Extract number from "THE_0000005" -> 5
                    $lastNumber = (int) str_replace($hotelPrefix.'_', '', $lastReservation->reservation_number);
                    $nextId = $lastNumber + 1;
                } else {
                    $nextId = 1;
                }

                // 3. Generate the formatted string: THE_0000001
                $reservation->reservation_number = $hotelPrefix.'_'.str_pad($nextId, 7, '0', STR_PAD_LEFT);
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
        return (float) $this->folios()
            ->get(['amount', 'base_amount'])
            ->sum(fn (ReservationFolio $folio): float => (float) ($folio->base_amount ?? $folio->amount));
    }

    public function getTotalFolioDebitsAttribute(): float
    {
        return (float) $this->folios()
            ->where('type', 'debit')
            ->get(['amount', 'base_amount'])
            ->sum(fn (ReservationFolio $folio): float => (float) ($folio->base_amount ?? $folio->amount));
    }

    public function getTotalFolioCreditsAttribute(): float
    {
        return (float) $this->folios()
            ->where('type', 'credit')
            ->get(['amount', 'base_amount'])
            ->sum(fn (ReservationFolio $folio): float => (float) ($folio->base_amount ?? $folio->amount));
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
