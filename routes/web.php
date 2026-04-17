<?php

use Illuminate\Support\Facades\Route;
use App\Models\HotelRoom;
use Illuminate\Http\Request;
use App\Filament\Pages\Reservations;
use App\Http\Controllers\POSInvoiceController;

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
});
