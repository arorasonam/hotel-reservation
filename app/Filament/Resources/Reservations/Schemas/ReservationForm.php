<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('status')
                    ->required()
                    ->default('confirmed'),
                TextInput::make('room_no'),
                DatePicker::make('check_in'),
                DatePicker::make('check_out'),
                TextInput::make('nights')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('adults')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('title'),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('rate')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('outstanding')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('source'),
                TextInput::make('ref_id'),
            ]);
    }
}
