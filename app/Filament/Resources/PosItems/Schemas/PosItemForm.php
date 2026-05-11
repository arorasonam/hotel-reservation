<?php

namespace App\Filament\Resources\PosItems\Schemas;

use App\Services\TaxService;
use Filament\Forms\Components\Select;
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
                Select::make('tax_category')
                    ->label('Tax Category')
                    ->options([
                        TaxService::ITEM_CATEGORY_STANDARD_RESTAURANT => 'Standard restaurant',
                        'packaged_food' => 'Packaged food',
                        'processed_food_12' => 'Processed food 12%',
                        'processed_food_18' => 'Processed food 18%',
                        'aerated_drinks_luxury' => 'Aerated drinks / luxury items',
                        'fresh_staples' => 'Fresh staples / exempt',
                    ])
                    ->default(TaxService::ITEM_CATEGORY_STANDARD_RESTAURANT)
                    ->required(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
