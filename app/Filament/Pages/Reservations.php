<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Reservations extends Page
{
    protected string $view = 'filament.pages.reservations-calendar';

    // protected $listeners = [
    //     'openReservationModal',
    // ];

    public function getViewData(): array
    {
        $groupedRooms = \App\Models\RoomType::with('rooms')->get()->map(function ($type) {
            return [
                'code' => $type->code,
                'label' => $type->name,
                'totalRooms' => $type->rooms->count(),
                'rooms' => $type->rooms->map(function ($room) {
                    return [
                        'room_number' => $room->room_number,
                        'status' => $room->status, // Ensure this column exists in your rooms table
                    ];
                })->toArray(),
            ];
        });
        return [
            'hotels' => $this->getHotels(),
            'roomTypes' => $this->getRoomTypes(),
            'groupedRooms' => $groupedRooms,
            'totalVacant' => \App\Models\HotelRoom::where('status', 'vacant')->count(),
        ];
    }

    // public $selectedDate;
    // public $selectedRoom;

    // public function openReservationModal($data)
    // {
    //     $this->selectedDate = $data['date'];
    //     $this->selectedRoom = $data['resourceId'];

    //     $this->dispatch('open-modal', id: 'reservation-modal');
    // }

    // public function save()
    // {
    //     \App\Models\Reservation::create([
    //         'room_id' => $this->selectedRoom,
    //         'check_in' => $this->selectedDate,
    //         'check_out' => now()->addDay(),
    //         'status' => 'booked',
    //     ]);

    //     $this->dispatch('close-modal', id: 'reservation-modal');
    // }

    public function getRooms()
    {
        return \App\Models\Hotel::all()->map(fn($room) => [
            'id' => $room->id,
            'title' => $room->name,
        ]);
    }

    public function getHotels()
    {
        return \App\Models\Hotel::all()->map(fn($hotel) => [
            'id' => $hotel->id,
            'name' => $hotel->name,
        ]);
    }

    public function getRoomTypes()
    {
        return \App\Models\RoomType::all()->map(fn($type) => [
            'id' => $type->id,
            'code' => $type->code,
            'name' => $type->name
        ]);
    }

    // public function getReservations()
    // {
    //     return \App\Models\Reservation::all()->map(fn($res) => [
    //         'id' => $res->id,
    //         'resourceId' => $res->room_id,
    //         'start' => $res->check_in,
    //         'end' => $res->check_out,
    //         'title' => $res->status,
    //         'status' => $res->status,
    //     ]);
    // }
}
