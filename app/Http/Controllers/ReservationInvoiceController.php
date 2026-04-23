<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationRoomDetail;
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
            'roomCategories.roomType',
            'roomCategories.mealPlan',
            'roomCategories.roomDetails.folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
            'folios' => fn ($query) => $query->with('reservationRoomDetail')->orderBy('posted_at')->orderBy('id'),
        ]);

        return view('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarize($reservation),
            'folioTitle' => 'All Folios',
            'folioScope' => 'all',
        ]);
    }

    public function printRoomFolio(Reservation $reservation, ReservationRoomDetail $reservationRoomDetail): View
    {
        abort_unless((int) $reservationRoomDetail->category?->reservation_id === (int) $reservation->getKey(), 404);

        $reservation->load(['hotel', 'reservationGuests']);
        $reservationRoomDetail->load([
            'category.roomType',
            'category.mealPlan',
            'folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
        ]);

        return view('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarizeReservationRoomDetail($reservationRoomDetail),
            'folioTitle' => 'Room '.$reservationRoomDetail->room_number.' Folio',
            'folioScope' => 'room',
            'selectedRoom' => $reservationRoomDetail,
        ]);
    }

    public function printMasterFolio(Reservation $reservation): View
    {
        $reservation->load([
            'hotel',
            'reservationGuests',
            'folios' => fn ($query) => $query->whereNull('reservation_room_id')->orderBy('posted_at')->orderBy('id'),
        ]);

        return view('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarizeMasterFolio($reservation),
            'folioTitle' => 'Master Folio',
            'folioScope' => 'master',
        ]);
    }

    public function download(Reservation $reservation): Response
    {
        $reservation->load([
            'hotel',
            'reservationGuests',
            'roomCategories.roomType',
            'roomCategories.mealPlan',
            'roomCategories.roomDetails.folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
            'folios' => fn ($query) => $query->with('reservationRoomDetail')->orderBy('posted_at')->orderBy('id'),
        ]);

        $pdf = Pdf::loadView('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarize($reservation),
            'folioTitle' => 'All Folios',
            'folioScope' => 'all',
        ]);

        return $pdf->download('reservation_invoice_'.$reservation->reservation_number.'.pdf');
    }
}
