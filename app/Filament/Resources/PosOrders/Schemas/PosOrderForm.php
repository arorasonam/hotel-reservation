<?php

namespace App\Filament\Resources\PosOrders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PosOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hotel_id')
                    ->tel()
                    ->default(null),
                TextInput::make('reservation_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('guest_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('room_id')
                    ->default(null),
                TextInput::make('pos_outlet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('order_number')
                    ->required(),
                Select::make('order_type')
                    ->options(['room_charge' => 'Room charge', 'walk_in' => 'Walk in', 'takeaway' => 'Takeaway'])
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('grand_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'confirmed' => 'Confirmed', 'paid' => 'Paid', 'cancelled' => 'Cancelled'])
                    ->default('draft')
                    ->required(),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
            ]);
    }
}
