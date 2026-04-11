<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PosOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'PosOrders';

    protected static ?string $relatedResource = ReservationResource::class;

    public function table(Table $table): Table
    {
          return $table
            ->columns([
                TextColumn::make('order_number'),

                TextColumn::make('grand_total')
                    ->money('INR'),

                TextColumn::make('status'),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
