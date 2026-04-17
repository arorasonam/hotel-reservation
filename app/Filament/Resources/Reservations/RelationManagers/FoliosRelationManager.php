<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class FoliosRelationManager extends RelationManager
{
    protected static string $relationship = 'Folios';
    
    protected static ?string $title = 'Transactions/Folio';

    protected static ?string $relatedResource = ReservationResource::class;

    public function table(Table $table): Table
    {
       return $table
        ->columns([

            TextColumn::make('source'),

            TextColumn::make('description'),

            TextColumn::make('amount')
                ->money('INR'),

            TextColumn::make('type'),

            TextColumn::make('posted_at')
                ->dateTime(),
        ])
        ->recordActions([])
        ->bulkActions([]);
    }
}
