<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Tables;

class PosOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'PosOrders';

    protected static ?string $title = 'POS Orders';

    // protected static ?string $relatedResource = ReservationResource::class;

    public function table(Table $table): Table
    {
          return $table
            ->columns([
                TextColumn::make('order_number'),

                TextColumn::make('order_type'),

                TextColumn::make('outlet.name'),
                
                TextColumn::make('grand_total')
                    ->money('INR'),

                TextColumn::make('tax_amount')
                    ->money('INR'),

                TextColumn::make('status'),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'confirmed' => 'Confirmed',
                        'paid'      => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
            ]);
    }
}
