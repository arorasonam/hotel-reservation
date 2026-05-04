<?php

namespace App\Models;

use App\Services\ReservationFolioService;
use App\Support\CurrencyDefaults;
use App\Support\MoneyConverter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPayment extends Model
{
    protected $fillable = [
        'pos_order_id',
        'reservation_id',
        'reservation_room_id',
        'reservation_room_detail_id',
        'payment_method',
        'amount',
        'currency_code',
        'exchange_rate',
        'base_currency_code',
        'base_amount',
        'transaction_reference',
        'paid_at',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'base_amount' => 'decimal:2',
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

    public function reservationRoomDetail(): BelongsTo
    {
        return $this->belongsTo(ReservationRoomDetail::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    protected static function booted(): void
    {
        static::saving(function (PosPayment $payment): void {
            $order = $payment->order;

            $payment->currency_code ??= $order?->currency_code ?? CurrencyDefaults::defaultCode();
            $payment->base_currency_code ??= $order?->base_currency_code ?? MoneyConverter::baseCurrencyForHotel($order?->hotel_id);
            $payment->exchange_rate ??= $order?->exchange_rate ?? MoneyConverter::exchangeRateToBase($payment->currency_code, $payment->base_currency_code);
            $payment->base_amount = MoneyConverter::toBase($payment->amount, $payment->exchange_rate);
        });

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

        app(ReservationFolioService::class)->syncPosPayment($this->load('order.reservation', 'order.reservationRoomDetail'));

        $order->refreshSettlementStatus();
    }
}
