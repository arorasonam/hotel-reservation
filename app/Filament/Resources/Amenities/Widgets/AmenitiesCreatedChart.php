<?php

namespace App\Filament\Resources\Amenities\Widgets;

use App\Filament\ChartTrait;
use App\Models\Amenity;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\TrendValue;

class AmenitiesCreatedChart extends ChartWidget
{
    use ChartTrait;

    protected ?string $heading = 'Amenities Timeline';
    public ?string $filter = 'year';

    protected function getData(): array
    {
        $dateRange = $this->getDateRange($this->filter);
        $dateFormat = $this->getDateFormat($this->filter);

        $trendCreated = $this->getTrendData(Amenity::class, $dateRange['start'], $dateRange['end']);
        $trendUpdated = $this->getTrendData(
            Amenity::query()->whereNotNull('updated_at'),
            $dateRange['start'],
            $dateRange['end']
        );

        return [
            'datasets' => [
                [
                    'label' => 'Created',
                    'data' => $trendCreated->map(fn(TrendValue $v) => $v->aggregate),
                    'borderColor' => 'rgba(251, 191, 36, 1)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Updated',
                    'data' => $trendUpdated->map(fn(TrendValue $v) => $v->aggregate),
                    'borderColor' => 'rgba(168, 85, 247, 1)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.2)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $trendCreated->map(fn(TrendValue $v) => Carbon::parse($v->date)->format($dateFormat)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
