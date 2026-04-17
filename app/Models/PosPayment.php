<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosPayment extends Model
{
    protected $fillable = [
        'pos_order_id',
        'payment_method',
        'amount',
        'transaction_reference',
        'paid_at',
        'received_by'
    ];

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }

    protected static function booted()
    {
        static::created(function ($payment) {

            $order = $payment->order ?? \App\Models\PosOrder::find($payment->pos_order_id);
         
            if (!$order) {
                return; // safely exit instead of breaking the process
            }

            // ROOM POSTING LOGIC
            if ($payment->payment_method === 'room_posting') {

                if (!$order->reservation_id) {
                    return;
                }

                ReservationFolio::firstOrCreate(
                    [
                        'source' => 'pos',
                        'source_id' => $order->id
                    ],

                    [
                        'reservation_id' => $order->reservation_id,
                        'description' => 'Restaurant POS Order #' . $order->id,
                        'amount' => $order->grand_total,
                        'type' => 'debit',
                        'posted_at' => now()
                    ]
                );

                $order->update([
                    'status' => 'confirmed'
                ]);

                return;
            }

            // NORMAL PAYMENT FLOW

            $paidAmount = $order->payments()->sum('amount');

            if ($paidAmount >= $order->grand_total) {

                $order->update([
                    'status' => 'paid'
                ]);
            }

        });
    }
}
