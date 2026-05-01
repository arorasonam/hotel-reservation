<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MorphToSelect;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Hotel Name')->required()->maxLength(255),
                        TextInput::make('slug')->label('Slug')->required()->maxLength(255),
                        // TextInput::make('chain_code')->label('Chain Code')->maxLength(255),
                        // TextInput::make('ref_id')
                        //     ->rules(['nullable', 'uuid'])
                        //     ->placeholder('Leave blank to auto-generate')
                        //     ->dehydrateStateUsing(fn($state) => filled($state) ? $state : \Illuminate\Support\Str::uuid()),
                        Select::make('hotel_group_id')
                            ->label('Hotel Group')
                            ->relationship('hotelGroup', 'name', function (Builder $query) {
                                $user = auth()->user();

                                // SuperAdmin can select from any group
                                if ($user->hasRole('super_admin')) {
                                    return $query;
                                }

                                // Hotel Admins are restricted to their own group ID
                                return $query->where('id', $user->hotel_group_id);
                            })
                            ->default(function () {
                                $user = auth()->user();

                                // Auto-fill for Hotel Admins to ensure data integrity
                                if (!$user->hasRole('super_admin')) {
                                    return $user->hotel_group_id;
                                }

                                return null;
                            })
                            // Optional: Disable for Hotel Admins so they cannot accidentally switch groups
                            ->disabled(fn() => !auth()->user()->hasRole('super_admin'))
                            ->dehydrated() // Ensures value is saved even if the field is disabled
                            ->required()
                            ->native(false)
                            ->searchable()
                    ]),
                Section::make('Address')
                    ->columns(2)
                    ->schema([
                        // TextInput::make('full_address')
                        //     ->label('Search Address')
                        //     ->id('address-autocomplete') // Assign a specific ID for JS
                        //     ->extraAttributes(['onfocus' => 'initAutocomplete()'])
                        //     ->columnSpanFull(),
                        TextInput::make('address.street')->label('Street')->columnSpanFull(),
                        TextInput::make('address.city')->label('City'),
                        TextInput::make('address.state')->label('State'),
                        TextInput::make('address.zip')->label('ZIP Code'),
                        TextInput::make('address.country')->label('Country'),
                        TextInput::make('address.coordinates.latitude')->label('Latitude')->numeric(),
                        TextInput::make('address.coordinates.longitude')->label('Longitude')->numeric(),
                    ]),


                Section::make('Location')
                    ->columns(1)
                    ->schema([
                        MorphToSelect::make('locationable')
                            ->label('Location')
                            ->required()
                            ->types([
                                MorphToSelect\Type::make(Country::class)->titleAttribute('name')->getOptionLabelFromRecordUsing(fn(Country $record) => $record->emoji . ' ' . $record->name),
                                MorphToSelect\Type::make(State::class)->titleAttribute('name')->getOptionLabelFromRecordUsing(fn(State $record) => $record->name . ' (' . $record->country->iso2 . ')'),
                                MorphToSelect\Type::make(City::class)->titleAttribute('name')->getOptionLabelFromRecordUsing(fn(City $record) => $record->name . ' (' . $record->country->iso2 . ')'),
                            ])
                            ->searchable()
                            ->preload(),
                    ]),


                Section::make('Ratings & Scores')
                    ->columns(3)
                    ->schema([
                        TextInput::make('rating')->label('Rating')->numeric()->nullable(),
                        TextInput::make('user_ratings_total')->label('Total Reviews')->numeric()->nullable(),
                        TextInput::make('recommended_score')->label('Recommended Score')->numeric()->nullable(),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact.telephone')->label('Telephone')->tel(),
                        TextInput::make('contact.email')->label('Email')->email(),
                        TextInput::make('contact.web')->label('Website')->url(),
                        TextInput::make('contact.fax')->label('Fax')->tel(),
                    ])->columnSpanFull(),


            ]);
    }
}
