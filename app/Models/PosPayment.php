<?php

namespace App\Models;

use App\Services\ReservationFolioService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPayment extends Model
{
    protected $fillable = [
        'pos_order_id',
        'reservation_id',
        'reservation_room_id',
        'payment_method',
        'amount',
        'transaction_reference',
        'paid_at',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationRoom(): BelongsTo
    {
        return $this->belongsTo(ReservationRoom::class);
    }

    protected static function booted(): void
    {
        static::saved(function (PosPayment $payment): void {
            $payment->syncFolio();
        });

        static::deleted(function (PosPayment $payment): void {
            app(ReservationFolioService::class)->deleteEntry('pos_payment', $payment->id, 'payment');

            $payment->order?->refreshSettlementStatus();
        });
    }

    public function syncFolio(): void
    {
        $order = $this->order()->first();

        if (! $order) {
            return;
        }

        if ($order->status === 'draft') {
            $order->forceFill([
                'status' => 'confirmed',
            ])->save();
        }

        app(ReservationFolioService::class)->syncPosPayment($this->load('order.reservation', 'order.reservationRoom'));

        $order->refreshSettlementStatus();
    }
}
