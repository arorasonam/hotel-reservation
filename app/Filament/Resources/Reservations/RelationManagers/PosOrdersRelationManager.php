<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'posOrders';

    protected static ?string $title = 'POS Orders';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number'),
                TextColumn::make('order_type'),
                TextColumn::make('outlet.name'),
                TextColumn::make('reservationRoomDetail.room_number')
                    ->label('Stay Room')
                    ->placeholder('N/A'),
                TextColumn::make('table_no')
                    ->placeholder('N/A'),
                TextColumn::make('grand_total')
                    ->money('INR'),
                TextColumn::make('tax_amount')
                    ->money('INR'),
                TextColumn::make('status'),
                TextColumn::make('settled_at')
                    ->dateTime()
                    ->placeholder('Pending'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),
            ]);
    }
}
