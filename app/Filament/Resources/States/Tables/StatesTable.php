<?php

namespace App\Filament\Resources\States\Tables;

use App\Filament\Resources\Countries\CountryResource;
use App\Models\City;
use App\Models\Country;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin')
                    ->weight('medium')
                    ->description(fn($record): string => $record->slug ?? 'No slug'),
                TextColumn::make('country.iso2')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-flag')
                    ->formatStateUsing(fn($record) => $record->country->emoji . ' ' . $record->country->iso2)
                    ->url(fn($record) => CountryResource::getUrl('view', ['record' => $record->country]))
                    ->openUrlInNewTab(false),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cities_count')
                    ->label('Cities')
                    ->counts('cities')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-building-office-2'),
                TextColumn::make('latitude')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('longitude')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-link')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
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
                    )
                    ->query(function ($query, $state) {
                        if (filled($state['value'])) {
                            $query->whereHas('country', function ($q) use ($state) {
                                $q->where('region', $state['value']);
                            });
                        }
                    }),

                SelectFilter::make('country')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(Country $record) => $record->emoji . ' ' . $record->name),

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
