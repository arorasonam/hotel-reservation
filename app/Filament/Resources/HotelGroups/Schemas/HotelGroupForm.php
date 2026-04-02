<?php

namespace App\Filament\Resources\HotelGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HotelGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
