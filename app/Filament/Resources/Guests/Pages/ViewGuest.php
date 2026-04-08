<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    
    public function getTitle(): string | HtmlString
    {
        $guest = $this->record;

        $image = $guest->profile_photo
            ? asset('storage/' . $guest->profile_photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($guest->first_name . ' ' . $guest->last_name);

        return new HtmlString("
            <div style='display:flex;align-items:center;gap:10px'>
                <img src='{$image}'
                    style='width:40px;height:40px;border-radius:50%;object-fit:cover;'>

                <span>{$guest->first_name} {$guest->last_name}</span>
            </div>
        ");
    }
}
