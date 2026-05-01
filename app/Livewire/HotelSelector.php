<?php

namespace App\Livewire;

use App\Models\Hotel;
use Livewire\Component;

class HotelSelector extends Component
{
    public ?string $selectedHotel = null;
    public $hotels = [];

    public function mount(): void
    {
        $this->hotels = Hotel::orderBy('name')->get();

        $this->selectedHotel = session('selected_hotel_id')
        ?? optional($this->hotels->first())->id;
        
        // Always persist to session
        session(['selected_hotel_id' => $this->selectedHotel]);
    }

    public function updatedSelectedHotel($value): void
    {
        // session(['selected_hotel_id' => $value ? (int) $value : null]);
        // $this->dispatch('hotelChanged', hotelId: $value);
        // $this->redirect(request()->header('Referer') ?? '/admin');

         session(['selected_hotel_id' => (string) $value]);
        $this->redirect(request()->header('Referer') ?? '/admin');
    }

    public function render()
    {
        return view('livewire.hotel-selector');
    }
}