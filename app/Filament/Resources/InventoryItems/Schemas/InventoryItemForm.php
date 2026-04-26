<?php

namespace App\Filament\Resources\InventoryItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hotel_id')
                    ->tel()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('unit')
                    ->required(),
                TextInput::make('current_stock')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('reorder_level')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
