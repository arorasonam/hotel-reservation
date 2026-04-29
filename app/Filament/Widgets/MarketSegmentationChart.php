<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class MarketSegmentationChart extends ChartWidget
{
    // Fix: Remove 'static' to match parent class signature
    protected ?string $heading = 'Market Segmentation';

    // Position it side-by-side with your other comparative charts
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        /** * For a Senior Lead implementation, you would query your 
         * Reservations model here and group by 'segment'.
         */
        return [
            'datasets' => [
                [
                    'label' => 'Market Share',
                    'data' => [45, 25, 20, 10], // Sample data
                    'backgroundColor' => [
                        '#10b981', // Emerald (Corporate)
                        '#f59e0b', // Amber (Leisure)
                        '#3b82f6', // Blue (Group)
                        '#8b5cf6', // Others (Staycation/etc)
                    ],
                ],
            ],
            'labels' => ['Corporate', 'Leisure', 'Group', 'Others'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
