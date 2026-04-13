<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity-widget';
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function getViewData(): array
    {
        return [
            // 'activities' => Activity::latest()->limit(10)->get(),
        ];
    }
}
