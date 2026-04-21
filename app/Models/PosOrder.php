<?php

namespace App\Models;

use App\Services\ReservationFolioService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class PosOrder extends Model
{
    protected $fillable = [
        'hotel_id',
        'reservation_id',
        'guest_id',
        'room_id',
        'pos_outlet_id',
        'table_no',
        'order_number',
        'order_type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'status',
        'settled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'settled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'room_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class);
    }

    protected static function booted(): void
    {
        static::creating(function (PosOrder $order): void {
            if (! $order->created_by) {
                $order->created_by = Auth::id();
            }
        });

        static::deleted(function (PosOrder $order): void {
            app(ReservationFolioService::class)->deleteEntriesForSource('pos_order', $order->id);
        });
    }

    public function refreshTotals(): void
    {
        $items = $this->items()->get();

        $subtotal = (float) $items->sum('subtotal');
        $taxAmount = (float) $items->sum('tax_amount');
        $discountAmount = (float) $this->discount_amount;
        $grandTotal = round($subtotal + $taxAmount - $discountAmount, 2);

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ]);

        $this->refreshSettlementStatus();
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function refreshSettlementStatus(): void
    {
        $paidAmount = (float) $this->payments()->sum('amount');

        if ((float) $this->grand_total <= 0) {
            $this->forceFill([
                'status' => 'paid',
                'settled_at' => now(),
            ])->save();

            return;
        }

        if ($paidAmount >= (float) $this->grand_total) {
            $this->forceFill([
                'status' => 'paid',
                'settled_at' => $this->settled_at ?? now(),
            ])->save();

            return;
        }

        if ($this->status === 'paid') {
            $this->forceFill([
                'status' => 'confirmed',
                'settled_at' => null,
            ])->save();
        }
    }
}
