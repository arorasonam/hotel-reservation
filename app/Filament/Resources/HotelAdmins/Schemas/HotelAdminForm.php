<?php

namespace App\Filament\Resources\HotelAdmins\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class HotelAdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('phone')
                ->tel()
                ->nullable()
                ->maxLength(20),

            Select::make('hotel_group_id')->label('Hotel Group')->required()->relationship('hotelGroup', 'name')->searchable()->preload(),

            TextInput::make('password')
                ->password()
                ->revealable()
                ->required(fn(string $operation) => $operation === 'create')
                ->minLength(8)
                ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->confirmed(),

            TextInput::make('password_confirmation')
                ->password()
                ->revealable()
                ->required(fn(string $operation) => $operation === 'create')
                ->dehydrated(false),
        ]);
    }
}
