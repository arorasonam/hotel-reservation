<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueTrendChart extends ChartWidget
{
    protected ?string $heading = 'All Revenue Trend';
    protected int | string | array $columnSpan = 'full'; // Occupy full width for trends

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Direct',
                    'data' => [600, 1200, 800, 2400, 1100], // Query your DB here
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'OTA',
                    'data' => [300, 500, 1200, 900, 1500],
                    'borderColor' => '#ef4444',
                ],
                [
                    'label' => 'Booking Engine',
                    'data' => [100, 200, 400, 300, 600],
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
