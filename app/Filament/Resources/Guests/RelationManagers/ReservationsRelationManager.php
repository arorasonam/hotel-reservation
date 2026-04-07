<?php

namespace App\Filament\Resources\Guests\RelationManagers;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    protected static ?string $title = 'Stay History';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('room.name'),

                TextColumn::make('checkin_date')->date(),

                TextColumn::make('checkout_date')->date(),

                TextColumn::make('status')->badge(),

            ]);
    }
}