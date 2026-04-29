<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ChannelMixChart extends ChartWidget
{
    protected ?string $heading = 'Channel Mix For All Revenue';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Revenue Share',
                    'data' => [54.8, 14.7, 30.5],
                    'backgroundColor' => ['#3b82f6', '#f59e0b', '#10b981'],
                ],
            ],
            'labels' => ['Website', 'Phone', 'OTA'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Creates the circular mix seen in your images
    }
}
