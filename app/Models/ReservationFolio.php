<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationFolio extends Model
{
    protected $fillable = [
        'reservation_id',
        'reservation_room_id',
        'reservation_room_detail_id',
        'source',
        'source_id',
        'source_key',
        'description',
        'reference',
        'notes',
        'amount',
        'tax_id',
        'tax_rule_id',
        'tax_name',
        'tax_type',
        'tax_percentage',
        'tax_amount',
        'type',
        'entry_type',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function getTaxBreakdownAttribute(): array
    {
        if ((float) $this->tax_amount <= 0 && (float) $this->tax_percentage <= 0) {
            return [];
        }

        return [[
            'name' => $this->tax_name ?? 'Tax',
            'type' => $this->tax_type ?? 'tax',
            'percentage' => (float) $this->tax_percentage,
            'amount' => (float) $this->tax_amount,
            'rule_id' => $this->tax_rule_id,
            'tax_id' => $this->tax_id,
        ]];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationRoom(): BelongsTo
    {
        return $this->belongsTo(ReservationRoom::class);
    }

    public function reservationRoomDetail(): BelongsTo
    {
        return $this->belongsTo(ReservationRoomDetail::class);
    }

    public function getSignedAmountAttribute(): float
    {
        $amount = (float) $this->amount;

        return $this->type === 'credit' ? -$amount : $amount;
    }
}
