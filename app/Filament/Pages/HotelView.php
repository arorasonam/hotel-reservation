<?php

namespace App\Filament\Pages;

use App\Models\Hotel;
use Filament\Pages\Page;
use Filament\Panel;

class HotelView extends Page
{
    protected string $view = 'filament.pages.hotel-view';
    protected static bool $shouldRegisterNavigation = false;

    public Hotel $record;

    /**
     * Use a generic variable name and default to null 
     * to prevent the "Unable to resolve dependency" error.
     */
    public function mount($record = null): void
    {
        if (blank($record)) {
            abort(404, 'Hotel ID is missing.');
        }

        // If $record is an object/model, get its ID. Otherwise, use as is.
        $id = $record instanceof \Illuminate\Database\Eloquent\Model
            ? $record->getKey()
            : $record;

        $this->record = Hotel::with(['amenities', 'descriptions', 'rooms', 'medias'])->findOrFail($id);
    }

    public static function getRoutePath(Panel $panel): string
    {
        // Keep the placeholder as {record} to match Filament's default expectations
        return '/hotel-view/{record}';
    }
}
