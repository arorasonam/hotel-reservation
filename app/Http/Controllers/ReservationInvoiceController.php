<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationRoom;
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
            'reservationRooms.roomType',
            'reservationRooms.mealPlan',
            'reservationRooms.folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
            'folios' => fn ($query) => $query->with('reservationRoom')->orderBy('posted_at')->orderBy('id'),
        ]);

        return view('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarize($reservation),
            'folioTitle' => 'All Folios',
            'folioScope' => 'all',
        ]);
    }

    public function printRoomFolio(Reservation $reservation, ReservationRoom $reservationRoom): View
    {
        abort_unless((int) $reservationRoom->reservation_id === (int) $reservation->getKey(), 404);

        $reservation->load(['hotel', 'reservationGuests']);
        $reservationRoom->load([
            'roomType',
            'mealPlan',
            'folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
        ]);

        return view('reservations.invoice', [
            'reservation' => $reservation,
            'summary' => $this->folioService->summarizeReservationRoom($reservationRoom),
            'folioTitle' => 'Room '.$reservationRoom->room_number.' Folio',
            'folioScope' => 'room',
            'selectedRoom' => $reservationRoom,
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
            'reservationRooms.roomType',
            'reservationRooms.mealPlan',
            'reservationRooms.folios' => fn ($query) => $query->orderBy('posted_at')->orderBy('id'),
            'folios' => fn ($query) => $query->with('reservationRoom')->orderBy('posted_at')->orderBy('id'),
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
