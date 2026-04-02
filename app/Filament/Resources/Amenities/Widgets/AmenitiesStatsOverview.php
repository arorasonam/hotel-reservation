<?php

namespace App\Filament\Resources\Amenities\Widgets;

use App\Filament\ChartTrait;
use App\Models\Amenity;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AmenitiesStatsOverview extends BaseStatsOverviewWidget
{
    use ChartTrait;

    protected function getStats(): array
    {
        $today = Amenity::whereDate('created_at', Carbon::today())->count();
        $last7 = Amenity::whereDate('created_at', '>=', Carbon::today()->subDays(6))->count();
        $last30 = Amenity::whereDate('created_at', '>=', Carbon::today()->subDays(29))->count();
        $total = Amenity::count();



        return [
            Stat::make('Today', $today)->icon('heroicon-o-calendar')->color('info'),
            Stat::make('Last 7 days', $last7)->icon('heroicon-o-chart-bar')->color('primary'),
            Stat::make('Last 30 days', $last30)->icon('heroicon-o-chart-bar')->color('warning'),
            Stat::make('Total', $total)->icon('heroicon-o-hashtag')->color('success'),

        ];
    }
}
