<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Support\CurrencyDefaults;
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
                    ->formatStateUsing(fn ($state, $record): string => ($record->currency_code ?? CurrencyDefaults::defaultCode()).' '.number_format((float) $state, 2)),
                TextColumn::make('base_amount')
                    ->label('Base Total')
                    ->formatStateUsing(fn ($state, $record): string => ($record->base_currency_code ?? CurrencyDefaults::defaultCode()).' '.number_format((float) $state, 2)),
                TextColumn::make('tax_amount')
                    ->formatStateUsing(fn ($state, $record): string => ($record->currency_code ?? CurrencyDefaults::defaultCode()).' '.number_format((float) $state, 2)),
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
