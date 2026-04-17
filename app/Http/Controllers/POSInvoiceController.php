<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PosOrder;

class POSInvoiceController extends Controller
{
    public function print($id)
    {
        $order = PosOrder::with([
            'items.item',
            'payments',
            'guest',
            'outlet'
        ])->findOrFail($id);

        return view('pos.invoice', compact('order'));
    }

    public function download($id)
    {
        $order = PosOrder::with([
            'items.item',
            'payments',
            'guest',
            'outlet'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pos.invoice', compact('order'));

        return $pdf->download("invoice_{$order->id}.pdf");
    }
}