<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\Can;
use App\Filament\Pages\Reservations as CalendarPage;
use Filament\Actions;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_calendar')
                ->label('Calendar View')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->url(fn() => CalendarPage::getUrl()), // This will now resolve to the new slug
            CreateAction::make(),
        ];
    }
}
