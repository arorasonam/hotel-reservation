<?php

namespace App\Filament\Widgets;

use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Reservation;
// Only need this one base class
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget // Extend BaseWidget
{
    // REMOVE the protected string $view line entirely.
    // BaseWidget handles the view for you automatically.
   
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Hotels', Hotel::count())
                ->description('Active properties')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Total Reservations', Reservation::count())
                ->description('Confirmed bookings')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Total Guests', Guest::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
