<?php

namespace App\Filament\Resources\Amenities\Pages;

use App\Filament\Resources\Amenities\AmenityResource;
use App\Filament\Resources\Amenities\Widgets\AmenitiesStatsOverview;
use App\Filament\Resources\Amenities\Widgets\AmenitiesCreatedChart;
use App\Filament\Resources\Amenities\Widgets\AmenitiesHotelsDonut;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAmenities extends ManageRecords
{
    protected static string $resource = AmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('md'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AmenitiesStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AmenitiesCreatedChart::class,
            AmenitiesHotelsDonut::class,
        ];
    }
}
