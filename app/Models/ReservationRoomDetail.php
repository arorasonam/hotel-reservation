<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function category(): BelongsTo
    {
        return $this->belongsTo(ReservationRoomCategory::class, 'category_id');
    }

    public function reservationRoomCategory(): BelongsTo
    {
        return $this->category();
    }

    public function folios(): HasMany
    {
        return $this->hasMany(ReservationFolio::class, 'reservation_room_detail_id');
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'reservation_room_detail_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class, 'reservation_room_detail_id');
    }

    public function isCheckedIn(): bool
    {
        return strtolower((string) $this->status) === 'checked_in';
    }

    public function getDisplayNameAttribute(): string
    {
        $reservation = $this->category?->reservation;
        $guest = $reservation?->reservationGuests?->first();
        $guestName = trim(($guest?->first_name ?? '').' '.($guest?->last_name ?? ''));

        return trim(sprintf(
            'Room %s - %s - #%s',
            $this->room_number ?: 'Auto',
            $guestName ?: 'Guest',
            $reservation?->reservation_number ?: $this->category?->reservation_id,
        ));
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
