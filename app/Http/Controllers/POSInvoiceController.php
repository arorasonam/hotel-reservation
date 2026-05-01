<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class POSInvoiceController extends Controller
{
    public function print($id)
    {
        $order = PosOrder::with([
            'hotel',
            'items.item',
            'payments',
            'guest',
            'outlet',
            'reservationRoomDetail',
        ])->findOrFail($id);

        return view('pos.invoice', compact('order'));
    }

    public function download($id)
    {
        $order = PosOrder::with([
            'hotel',
            'items.item',
            'payments',
            'guest',
            'outlet',
            'reservationRoomDetail',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pos.invoice', compact('order'));

        return $pdf->download("invoice_{$order->id}.pdf");
    }
}
