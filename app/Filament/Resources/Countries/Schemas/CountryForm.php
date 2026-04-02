<?php

namespace App\Filament\Resources\Countries\Schemas;

use App\Models\Country;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Country name and identification codes')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('Enter country name')
                            ->columnSpan(3),
                        TextInput::make('iso2')
                            ->label('ISO2 Code')
                            ->required()
                            ->length(2)
                            ->unique(ignoreRecord: true)
                            ->extraAttributes(['style' => 'text-transform: uppercase'])
                            ->prefixIcon('heroicon-o-flag')
                            ->placeholder('US'),
                        TextInput::make('iso3')
                            ->label('ISO3 Code')
                            ->required()
                            ->length(3)
                            ->unique(ignoreRecord: true)
                            ->extraAttributes(['style' => 'text-transform: uppercase'])
                            ->prefixIcon('heroicon-o-flag')
                            ->placeholder('USA'),
                        TextInput::make('emoji')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('🇺🇸'),
                    ]),

                Section::make('Location Details')
                    ->description('Capital city and regional information')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        TextInput::make('capital')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->placeholder('Enter capital city')
                            ->columnSpan(2),
                        Select::make('region')
                            ->required()
                            ->options(
                                fn() => Country::query()
                                    ->whereNotNull('region')
                                    ->where('region', '!=', '')
                                    ->groupBy('region')
                                    ->orderBy('region')
                                    ->pluck('region', 'region')
                                    ->toArray()
                            )
                            ->searchable()
                            ->prefixIcon('heroicon-o-map')
                            ->placeholder('Select region')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('subregion', null)),
                        Select::make('subregion')
                            ->required()
                            ->options(
                                fn(Get $get) => Country::query()
                                    ->whereNotNull('subregion')
                                    ->where('subregion', '!=', '')
                                    ->when($get('region'), function ($query, $region) {
                                        $query->where('region', $region);
                                    })
                                    ->groupBy('subregion')
                                    ->orderBy('subregion')
                                    ->pluck('subregion', 'subregion')
                                    ->toArray()
                            )
                            ->searchable()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Select subregion')
                            ->native(false)
                            ->disabled(fn(Get $get) => !$get('region'))
                            ->helperText('Select region first'),
                    ]),

                Section::make('Additional Information')
                    ->description('Currency, timezone and reference data')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(10)
                            ->extraAttributes(['style' => 'text-transform: uppercase'])
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->placeholder('USD'),
                        Textarea::make('timezones')
                            ->required()
                            ->rows(3)
                            ->placeholder('{"timezone":"America/New_York"}')
                            ->helperText('Enter timezone data in JSON format')
                            ->formatStateUsing(fn($state) => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT))
                            ->dehydrateStateUsing(fn($state) => $state)
                            ->columnSpan(2),
                        TextInput::make('ref_id')
                            ->label('Reference ID')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('External reference ID')
                            ->helperText('Cannot be changed after creation')
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
