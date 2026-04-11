<?php

namespace App\Filament\Resources\PosItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PosItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pos_outlet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('pos_category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('tax_percentage')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
