<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PosOrder extends Model
{
    protected $fillable = [
        'hotel_id',
        'reservation_id',
        'guest_id',
        'room_id',
        'pos_outlet_id',
        'order_number',
        'order_type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'status',
        'created_by'
    ];
    
    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function outlet()
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(HotelRoom::class, 'room_id');
    }
    
    protected static function booted()
    {
        static::creating(function ($order) {
            if (!$order->created_by) {
                $order->created_by = Auth::id();
            }
        });
    }

    public function refreshTotals()
    {
        // $subtotal = $this->items->sum(fn ($item) =>
        //     $item->price * $item->quantity
        // );

        $subtotal = 0;
        $taxAmount = 0;
        $grandTotal = 0;

        foreach ($this->items ?? [] as $item) {
            $subtotal += $item->subtotal ?? 0;
            $taxAmount += $item->tax_amount ?? 0;
            $grandTotal += $item->total ?? 0;
        }

        $this->update([
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
            'tax_amount' => $taxAmount
        ]);
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
