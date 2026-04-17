<?php

namespace App\Filament\Resources\Taxes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('percentage')
                    ->required()
                    ->numeric(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
