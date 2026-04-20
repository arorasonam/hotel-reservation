<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\ReservationFolioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReservationInvoiceController extends Controller
{
    public function __construct(
        private readonly ReservationFolioService $folioService,
    ) {}

    public function print(Reservation $reservation): View
    {
        $reservation->load([
            'hotel',
            'reservationGuests',
            'folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
        ]);

        return view('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarize($reservation),
        ]);
    }

    public function download(Reservation $reservation): Response
    {
        $reservation->load([
            'hotel',
            'reservationGuests',
            'folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
        ]);

        $pdf = Pdf::loadView('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarize($reservation),
        ]);

        return $pdf->download('reservation_invoice_'.$reservation->reservation_number.'.pdf');
    }
}
