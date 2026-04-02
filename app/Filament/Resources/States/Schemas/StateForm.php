<?php

namespace App\Filament\Resources\States\Schemas;

use App\Models\State;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('State Information')
                    ->description('Enter the state details and location')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Enter state name')
                            ->columnSpan(2),

                        Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->emoji . ' ' . $record->name)
                            ->prefixIcon('heroicon-o-globe-alt'),

                        Select::make('type')
                            ->required()
                            ->options(
                                fn() => State::query()
                                    ->whereNotNull('type')
                                    ->where('type', '!=', '')
                                    ->groupBy('type')
                                    ->orderBy('type')
                                    ->pluck('type', 'type')
                                    ->toArray()
                            )
                            ->searchable()
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('Select or enter state type')
                            ->allowHtml()
                            ->native(false),

                        TextInput::make('slug')
                            ->nullable()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('Auto-generated from name')
                            ->columnSpan(2),
                    ]),

                Section::make('Geographic Coordinates')
                    ->description('Latitude and longitude information')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->nullable()
                            ->numeric()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Enter latitude'),

                        TextInput::make('longitude')
                            ->nullable()
                            ->numeric()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Enter longitude'),
                    ]),

                Section::make('Additional Information')
                    ->description('Reference data')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(1)
                    ->schema([
                        TextInput::make('ref_id')
                            ->label('Reference ID')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('External reference ID')
                            ->helperText('Cannot be changed after creation'),
                    ]),
            ]);
    }
}
