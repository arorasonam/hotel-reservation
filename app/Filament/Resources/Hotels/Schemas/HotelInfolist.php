<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\States\StateResource;
use App\Helpers\FilamentHelperLocationable;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class HotelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Hotel')
                            ->icon('heroicon-o-building-office')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('id')->label('ID')->icon('heroicon-o-hashtag')->badge()->copyable(),
                                TextEntry::make('name')->label('Name')->icon('heroicon-o-building-office')->weight('medium'),
                                TextEntry::make('slug')->label('Slug')->icon('heroicon-o-link')->placeholder('-'),
                                // TextEntry::make('chain_code')->label('Chain Code')->badge()->placeholder('-'),
                                // TextEntry::make('ref_id')->label('Ref ID')->placeholder('-'),
                                TextEntry::make('hotel_group_id')->label('Hotel Group ID')->placeholder('-'),
                            ]),

                        Section::make('Scores & Reviews')
                            ->icon('heroicon-o-star')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('rating')->label('Rating')->numeric()->placeholder('-')->badge()->color('warning'),
                                TextEntry::make('user_ratings_total')->label('Reviews')->numeric()->placeholder('-')->badge()->color('info'),
                                TextEntry::make('recommended_score')->label('Recommended Score')->numeric()->placeholder('-')->badge()->color('primary'),
                            ]),
                    ]),
                FilamentHelperLocationable::getLocationableInfolistEntries(),

                Section::make('Contact')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('contact.telephone')->label('Telephone')->icon('heroicon-o-phone')->placeholder('-'),
                        TextEntry::make('contact.fax')->label('Fax')->icon('heroicon-o-printer')->placeholder('-'),
                        TextEntry::make('contact.email')->label('Email')->icon('heroicon-o-envelope')->placeholder('-'),
                        TextEntry::make('contact.web')->label('Website')->icon('heroicon-o-globe-alt')->url(fn($record) => $record->contact->web ?? null)->openUrlInNewTab(true)->placeholder('-'),
                    ]),

                Section::make('Address')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('address.street')->label('Street')->icon('heroicon-o-map')->placeholder('-'),
                        TextEntry::make('address.city')->label('City')->icon('heroicon-o-building-office-2')->placeholder('-'),
                        TextEntry::make('address.state')->label('State')->icon('heroicon-o-map-pin')->placeholder('-'),
                        TextEntry::make('address.zip')->label('ZIP')->icon('heroicon-o-hashtag')->placeholder('-'),
                        TextEntry::make('address.country')->label('Country')->icon('heroicon-o-globe-alt')->placeholder('-'),
                        TextEntry::make('address.coordinates.latitude')->label('Latitude')->icon('heroicon-o-map')->placeholder('-'),
                        TextEntry::make('address.coordinates.longitude')->label('Longitude')->icon('heroicon-o-map')->placeholder('-'),
                        TextEntry::make('address.coordinates')->label('Google Maps')->icon('heroicon-o-map')->visible(fn($record) => isset($record->address->coordinates->latitude, $record->address->coordinates->longitude))
                            ->url(fn($record) => isset($record->address->coordinates->latitude, $record->address->coordinates->longitude)
                                ? 'https://www.google.com/maps/search/?api=1&query=' . $record->address->coordinates->latitude . ',' . $record->address->coordinates->longitude
                                : null)
                            ->openUrlInNewTab(true)
                            ->formatStateUsing(fn($record) => 'Open in Google Maps')
                            ->color('info'),
                    ]),

                Section::make('Timestamps')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('Created at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->label('Updated at')->dateTime()->placeholder('-'),
                    ]),
            ]);
    }
}
