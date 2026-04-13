<?php

namespace App\Filament\Pages;

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            QuickActionsWidget::class,
            StatsOverview::class,
            RecentActivityWidget::class,
        ];
    }
}
