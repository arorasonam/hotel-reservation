<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Models\Reservation;
use App\Models\Hotel;
use BackedEnum;
use UnitEnum;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static UnitEnum|string|null $navigationGroup = 'Management';
    protected string $view = 'filament.pages.reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Report Filters')
                ->schema([
                    DatePicker::make('start_date')->required()->live(),
                    DatePicker::make('end_date')->required()->live(),
                    Select::make('hotel_id')
                        ->options(fn() => Hotel::all()->pluck('name', 'id'))
                        ->live()
                        ->placeholder('All Properties'),
                ])->columns(3),
        ];
    }

    /**
     * Logic to calculate report data based on current filters
     */
    protected function getReportDataProperty()
    {
        $formData = $this->form->getState();

        return Reservation::query()
            ->whereBetween('check_in', [$formData['start_date'], $formData['end_date']])
            ->when($formData['hotel_id'], fn($q) => $q->where('hotel_id', $formData['hotel_id']))
            ->with(['roomCategories.roomDetails', 'hotel'])
            ->get();
    }
}
