<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\RevenueTrendChart;
use App\Filament\Widgets\OccupancyTrendChart;
use App\Filament\Widgets\ChannelMixChart;
use App\Filament\Widgets\MarketSegmentationChart;
use BackedEnum;
use UnitEnum;

class ReservationInsights extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static UnitEnum|string|null $navigationGroup = 'Reservation Management';
    protected static ?string $title = 'Reservation Insights';

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueTrendChart::class,
            ChannelMixChart::class,
            OccupancyTrendChart::class,
            MarketSegmentationChart::class,
        ];
    }


    // Set columns to 2 to allow charts to sit side-by-side like your screenshots
    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
