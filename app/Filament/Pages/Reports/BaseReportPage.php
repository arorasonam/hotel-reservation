<?php

// app/Filament/Pages/Reports/BaseReportPage.php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class BaseReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    // Shared filter state across all reports
    public ?string $date_from = null;

    public ?string $date_to = null;

    public ?string $outlet_id = null;

    protected string $view = 'filament.pages.reports.base-report';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->hasAnyRole(['super_admin', 'hotel_admin'])
            || $user?->can('View:PosReports'));
    }

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();
    }

    // Each report implements its own stats and table data
    abstract public function getStats(): array;

    abstract public function getTableData(): Collection;

    abstract public function getTableColumns(): array;

    abstract public function getExportClass(): string;

    protected function dateRange(): array
    {
        return [
            Carbon::parse($this->date_from)->startOfDay(),
            Carbon::parse($this->date_to)->endOfDay(),
        ];
    }

    public function export(): BinaryFileResponse
    {
        $class = $this->getExportClass();
        $filename = class_basename($class).'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new $class($this->date_from, $this->date_to, $this->outlet_id),
            $filename
        );
    }
}
