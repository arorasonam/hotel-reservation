<?php

namespace App\Filament\Resources\Hotels\Tables;

use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\States\StateResource;
use App\Helpers\FilamentHelper;
use App\Helpers\FilamentHelperLocationable;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true)->searchable(),
                TextColumn::make('name')->searchable()->sortable()->icon('heroicon-o-building-office')->weight('medium')->description(fn($record): string => $record->slug ?? 'No slug'),
                TextColumn::make('hotelGroup.name')->label('Hotel Group')->searchable()->sortable()->badge()->listWithLineBreaks()->limitList(2)->expandableLimitedList()->searchable()->toggleable(),
                ...FilamentHelperLocationable::getLocationableTableColumns(),
                TextColumn::make('rating')->numeric()->sortable()->badge()->color('warning')->icon('heroicon-o-star')->default('-')->toggleable(),
                TextColumn::make('user_ratings_total')->label('Reviews')->numeric()->sortable()->badge()->default('-')->color('info')->icon('heroicon-o-chat-bubble-left-right')->toggleable(),
                TextColumn::make('rooms_count')->label('Rooms')->counts('rooms')->sortable()->badge()->color('success')->icon('heroicon-o-home')->toggleable(),
                TextColumn::make('amenities.name')->label('Amenities')->badge()->listWithLineBreaks()->limitList(2)->expandableLimitedList()->searchable()->toggleable(),
                TextColumn::make('recommended_score')->label('Score')->numeric()->sortable()->badge()->color('primary')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('chain_code')->searchable()->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ref_id')->label('Ref ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->icon('heroicon-o-clock')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->icon('heroicon-o-clock')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FilamentHelperLocationable::getCountrySelectFilterTable(),
                FilamentHelperLocationable::getStateSelectFilterTable(),
                FilamentHelperLocationable::getCitySelectFilterTable(),
                DateRangeFilter::make('created_at'),
                DateRangeFilter::make('updated_at'),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('View')->color('info'),
                EditAction::make()->iconButton()->tooltip('Edit')->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No hotels yet')
            ->emptyStateDescription('Create your first hotel to get started.')
            ->emptyStateIcon('heroicon-o-building-office');
    }
}
