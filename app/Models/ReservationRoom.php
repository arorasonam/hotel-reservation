<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationRoom extends Model
{
    protected $table = 'reservation_rooms';

    protected $guarded = [];

    protected $fillable = [
        'reservation_id',
        'room_type_id',
        'meal_plan_id',
        'room_number', // Stores 'Auto' or specific number
        'check_in',
        'check_out',
        'rate',
        'currency_code',
        'exchange_rate_used',
        'base_rate',
        'nights',
        'adults',
        'children',
        'infant',
        'status', // For partial check-in logic
        'checked_in_at',
        'checked_out_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'rate' => 'decimal:2',
            'exchange_rate_used' => 'decimal:6',
            'base_rate' => 'decimal:2',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ReservationRoom $reservationRoom): void {
            $reservation = $reservationRoom->reservation;
            $currencyCode = $reservationRoom->currency_code ?: ($reservation?->currency_code ?? 'INR');
            $rate = (float) ($reservationRoom->exchange_rate_used ?: ($reservation?->exchange_rate_used ?? app(CurrencyService::class)->getRate($currencyCode)));

            $reservationRoom->currency_code = $currencyCode;
            $reservationRoom->exchange_rate_used = $rate;
            $reservationRoom->base_rate = app(CurrencyService::class)->toBase((float) $reservationRoom->rate, $rate);
        });
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }

    public function folios(): HasMany
    {
        return $this->hasMany(ReservationFolio::class);
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class);
    }

    public function isCheckedIn(): bool
    {
        return strtolower((string) $this->status) === 'checked_in';
    }

    public function getDisplayNameAttribute(): string
    {
        $reservation = $this->reservation;
        $guest = $reservation?->reservationGuests?->first();
        $guestName = trim(($guest?->first_name ?? '').' '.($guest?->last_name ?? ''));

        return trim(sprintf(
            'Room %s - %s - #%s',
            $this->room_number ?: 'Auto',
            $guestName ?: 'Guest',
            $reservation?->reservation_number ?: $this->reservation_id,
        ));
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
}
