<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationFolio extends Model
{
    protected $fillable = [
        'reservation_id',
        'reservation_room_id',
        'source',
        'source_id',
        'source_key',
        'description',
        'reference',
        'notes',
        'amount',
        'type',
        'entry_type',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationRoom(): BelongsTo
    {
        return $this->belongsTo(ReservationRoom::class);
    }

    public function getSignedAmountAttribute(): float
    {
        $amount = (float) $this->amount;

        return $this->type === 'credit' ? -$amount : $amount;
    }
}
