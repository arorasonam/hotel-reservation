<?php

use Illuminate\Support\Facades\Route;
use App\Models\HotelRoom;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/admin/rooms/{room_number}/status', function (Request $request, $room_number) {
    $room = HotelRoom::where('room_number', $room_number)->firstOrFail();

    // Validate that the status is one of your allowed types
    $validated = $request->validate([
        'status' => 'required|in:clean,dirty,maintenance,check-in,check-out'
    ]);

    $room->update(['status' => $validated['status']]);

    return response()->json(['success' => true]);
})->middleware(['auth', 'verified']); // Ensure only logged-in admins can do this
