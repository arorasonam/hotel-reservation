<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CountryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Country identification and location details')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('emoji')
                            ->label('Flag')
                            ->size('2xl'),
                        TextEntry::make('name')
                            ->icon('heroicon-o-globe-alt')
                            ->size('lg')
                            ->weight('bold')
                            ->color('primary')
                            ->columnSpan(2),
                        TextEntry::make('capital')
                            ->icon('heroicon-o-building-office-2')
                            ->placeholder('No capital'),
                        TextEntry::make('region')
                            ->icon('heroicon-o-map')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('subregion')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('No subregion'),
                    ]),

                Section::make('Country Codes & Currency')
                    ->description('ISO codes and currency information')
                    ->icon('heroicon-o-flag')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('iso2')
                            ->label('ISO2 Code')
                            ->icon('heroicon-o-flag')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('iso3')
                            ->label('ISO3 Code')
                            ->icon('heroicon-o-flag')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('currency')
                            ->icon('heroicon-o-currency-dollar')
                            ->badge()
                            ->color('warning'),
                    ]),

                Section::make('Additional Details')
                    ->description('Timezone and reference information')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('timezones')
                            ->icon('heroicon-o-clock')
                            ->formatStateUsing(fn($state) => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT))
                            ->placeholder('No timezone')
                            ->columnSpanFull(),
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
