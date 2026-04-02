<?php

namespace App\Filament\Resources\Amenities\Widgets;

use App\Filament\ChartTrait;
use App\Models\Amenity;
use Filament\Widgets\ChartWidget;

class AmenitiesHotelsDonut extends ChartWidget
{
    use ChartTrait;

    protected ?string $heading = 'Hotels per Amenity (Top 8)';

    protected function getData(): array
    {
        $topAmenities = Amenity::query()
            ->withCount('hotels')
            ->orderByDesc('hotels_count')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $topAmenities->pluck('hotels_count')->toArray(),
                    'backgroundColor' => ['#fbbf24', '#f59e0b', '#f97316', '#ef4444', '#ec4899', '#a855f7', '#8b5cf6', '#6366f1'],
                    'borderColor' => ['#fbbf24', '#f59e0b', '#f97316', '#ef4444', '#ec4899', '#a855f7', '#8b5cf6', '#6366f1'],
                    'hoverBackgroundColor' => ['#fcd34d', '#fbbf24', '#fb923c', '#f87171', '#f472b6', '#c084fc', '#a78bfa', '#818cf8'],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $topAmenities->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
