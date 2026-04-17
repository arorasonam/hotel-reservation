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
            $paidAmount = $order->payments()->sum('amount');
           
            if ($payment->payment_method !== 'room_posting' && $paidAmount >= $order->grand_total) {
                $order->update([
                    'status' => 'paid'
                ]);
            }

        });
    }
}
