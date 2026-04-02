<?php

namespace App\Filament;

use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

trait ChartTrait
{

    protected function getTrendData($model, Carbon $start, Carbon $end)
    {

        return Trend::{\is_string($model) ? 'model' : 'query'}($model)->between(start: $start, end: $end)->{$this->getDatePer($this->filter)}()->count();
    }


    protected function getTrendDataSum($model, string $sumColumn, Carbon $start, Carbon $end)
    {

        return Trend::{\is_string($model) ? 'model' : 'query'}($model)->between(start: $start, end: $end)->{$this->getDatePer($this->filter)}()->sum($sumColumn);
    }

    protected function getTotal($model, Carbon $start, Carbon $end)
    {
        return Trend::model($model)->between(start: $start, end: $end)->count();
    }


    protected function getDateRange(string $filter): array
    {
        switch ($filter) {
            case 'week':
                return ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()];
            case 'month':
                return ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()];
            case 'year':
                return ['start' => now()->startOfYear(), 'end' => now()->endOfYear()];
            case 'today':
            default:
                return ['start' => now()->startOfDay(), 'end' => now()->endOfDay()];
        }
    }

    protected function getDatePer(string $filter): string
    {
        switch ($filter) {
            case 'week':
                return  'perDay';
            case 'month':
                return 'perDay';
            case 'year':
                return 'perMonth';
            case 'today':
                return 'perHour';
            default:
                return 'perHour';
        }
    }

    protected function getDateFormat(string $filter): string
    {
        switch ($filter) {
            case 'week':
            case 'month':
                return 'd M';
            case 'year':
                return 'F';
            case 'today':
            default:
                return 'H:i';
        }
    }
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }
}
