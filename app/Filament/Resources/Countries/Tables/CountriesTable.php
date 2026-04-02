<?php

namespace App\Filament\Resources\Countries\Tables;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('emoji')
                    ->label('')
                    ->size('lg'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-globe-alt')
                    ->weight('medium')
                    ->description(fn($record): string => $record->capital ?? 'No capital'),
                TextColumn::make('iso2')
                    ->label('ISO2')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-flag'),
                TextColumn::make('iso3')
                    ->label('ISO3')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-map'),
                TextColumn::make('subregion')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
                TextColumn::make('capital')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency')
                    ->searchable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('states_count')
                    ->label('States')
                    ->counts('states')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('cities_count')
                    ->label('Cities')
                    ->counts('cities')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-building-office-2'),
                TextColumn::make('hotels_count')
                    ->label('Hotels')
                    ->counts('hotels')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-building-office-2'),
                TextColumn::make('ref_id')
                    ->label('Ref ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('region')
                    ->label('Region')
                    ->options(
                        fn() => Country::query()
                            ->whereNotNull('region')
                            ->where('region', '!=', '')
                            ->groupBy('region')
                            ->orderBy('region')
                            ->pluck('region', 'region')
                            ->toArray()
                    ),

                SelectFilter::make('states')
                    ->label('States')
                    ->relationship('states', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(State $record) => $record->name . ' (' . $record->country->iso2 . ')'),

                SelectFilter::make('cities')
                    ->label('Cities')
                    ->relationship('cities', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(City $record) => $record->name . ' (' . $record->country->iso2 . ')'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View')
                    ->color('info'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit')
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->defaultSort('name', 'asc')
            ->striped();
    }
}
