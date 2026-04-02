<?php

namespace App\Filament\Resources\States\Schemas;

use App\Filament\Resources\Countries\CountryResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('State identification and location details')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->icon('heroicon-o-map-pin')
                            ->size('lg')
                            ->weight('bold')
                            ->color('primary')
                            ->columnSpan(2),
                        TextEntry::make('type')
                            ->icon('heroicon-o-tag')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('country.name')
                            ->label('Country')
                            ->icon('heroicon-o-globe-alt')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($record) => $record->country->emoji . ' ' . $record->country->name)
                            ->url(fn($record) => CountryResource::getUrl('view', ['record' => $record->country]))
                            ->openUrlInNewTab(false)
                            ->columnSpan(2),
                        TextEntry::make('slug')
                            ->icon('heroicon-o-link')
                            ->placeholder('No slug')
                            ->color('gray'),
                    ]),

                Section::make('Geographic Coordinates')
                    ->description('Latitude and longitude information')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('latitude')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('-')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('longitude')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('-')
                            ->badge()
                            ->color('success'),
                    ]),

                Section::make('Additional Details')
                    ->description('Reference and system information')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('cities_count')
                            ->label('Cities Count')
                            ->state(fn($record) => $record->cities()->count())
                            ->icon('heroicon-o-building-office-2')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('ref_id')
                            ->label('Reference ID')
                            ->numeric()
                            ->icon('heroicon-o-link')
                            ->placeholder('-')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('Reference ID copied!')
                            ->copyMessageDuration(1500),
                        TextEntry::make('id')
                            ->label('ID')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('ID copied!')
                            ->copyMessageDuration(1500),
                    ]),

                Section::make('Timestamps')
                    ->description('Record creation and update times')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-clock')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->icon('heroicon-o-clock')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
