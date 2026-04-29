<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestReservations extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Booking Activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->when(!auth()->user()->hasRole('SuperAdmin'), function ($q) {
                        $q->whereHas('hotel', fn($h) => $h->where('hotel_group_id', auth()->user()->hotel_group_id));
                    })
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reservation_number')->label('REF ID')->weight('bold'),
                Tables\Columns\TextColumn::make('reservationGuests.full_name')->label('Guest Name'),
                Tables\Columns\TextColumn::make('hotel.name')->label('Property'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'checked_in',
                        'warning' => 'confirmed',
                        'danger' => 'checked_out',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('Booking Date')->dateTime('d M, H:i'),
            ]);
    }
}
