<?php

namespace App\Filament\Resources\PosCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PosCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pos_outlet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
