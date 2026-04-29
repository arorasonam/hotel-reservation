<?php

namespace App\Filament\Pages;

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\ReservationStats;
use App\Filament\Widgets\OccupancyChart;
use App\Filament\Widgets\LatestReservations;
use App\Filament\Widgets\WelcomeWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            StatsOverview::class,
            QuickActionsWidget::class,
            ReservationStats::class,
            OccupancyChart::class,
            LatestReservations::class,
        ];
    }
}
