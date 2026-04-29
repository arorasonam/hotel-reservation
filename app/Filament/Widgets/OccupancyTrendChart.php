<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class OccupancyTrendChart extends ChartWidget
{
    protected ?string $heading = 'Occupancy Trend';

    // Allows it to sit side-by-side with another chart
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Occupancy %',
                    'data' => [45, 52, 38, 65, 48, 70, 55], // Example percentages
                    'borderColor' => '#0ea5e9', // Blue
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                ],
            ],
            'labels' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
