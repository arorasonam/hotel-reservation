<?php

use App\Filament\Pages\Reservations;
use App\Http\Controllers\POSInvoiceController;
use App\Http\Controllers\ReservationInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])->prefix('filament')->group(function () {

    // Update room housekeeping status
    Route::post('/rooms/{roomNo}/status', [Reservations::class, 'updateRoomStatus'])
        ->name('filament.rooms.status');

    // Create a new reservation
    Route::post('/reservations', [Reservations::class, 'storeReservation'])
        ->name('filament.reservations.store');

    // Cancel a reservation
    Route::post('/reservations/{id}/cancel', [Reservations::class, 'cancelReservation'])
        ->name('filament.reservations.cancel');

    // Edit page (wire up to your EditReservation Filament resource)
    // Route::get('/reservations/{id}/edit', [...])
    //      ->name('filament.reservations.edit');
    Route::get('/pos/invoice/{id}', [POSInvoiceController::class, 'print'])
        ->name('pos.invoice.print');

    Route::get('/pos/invoice/{id}/download', [POSInvoiceController::class, 'download'])
        ->name('pos.invoice.download');

    Route::get('/reservations/{reservation}/invoice', [ReservationInvoiceController::class, 'print'])
        ->name('reservations.invoice.print');

    Route::get('/reservations/{reservation}/folios/master/print', [ReservationInvoiceController::class, 'printMasterFolio'])
        ->name('reservations.folios.master.print');

    Route::get('/reservations/{reservation}/folios/{reservationRoomDetail}/print', [ReservationInvoiceController::class, 'printRoomFolio'])
        ->name('reservations.folios.room.print');

    Route::get('/reservations/{reservation}/invoice/download', [ReservationInvoiceController::class, 'download'])
        ->name('reservations.invoice.download');
});
