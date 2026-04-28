<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReservationStats extends BaseWidget
{
    // FIX: Remove 'static' here
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $user = auth()->user();

        $query = Reservation::query();

        if (!$user->hasRole('SuperAdmin')) {
            $query->whereHas('hotel', fn($q) => $q->where('hotel_group_id', $user->hotel_group_id));
        }

        return [
            Stat::make('Arrivals Today', (clone $query)->where('check_in', $today)->count())
                ->description('Guests checking in')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),

            Stat::make('Departures Today', (clone $query)->where('check_out', $today)->count())
                ->description('Guests checking out')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning'),

            Stat::make('In-House', (clone $query)->where('status', 'checked_in')->count())
                ->description('Current occupancy')
                ->icon('heroicon-o-home-modern')
                ->color('primary'),
        ];
    }
}
