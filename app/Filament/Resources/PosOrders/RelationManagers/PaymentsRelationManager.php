<?php

namespace App\Filament\Resources\PosOrders\RelationManagers;

use App\Filament\Resources\PosOrders\PosOrderResource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    protected static ?string $relatedResource = PosOrderResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_method'),
                TextColumn::make('amount')
                    ->money('INR'),
                TextColumn::make('transaction_reference'),
                TextColumn::make('paid_at')
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make('payment')
                    ->label('Create Payment')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pos_order_id'] = $this->getOwnerRecord()->id;
                        $data['reservation_id'] = $this->getOwnerRecord()->reservation_id;
                        $data['received_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([])
            ->bulkActions([]);
    }

    public function form(Schema $schema): Schema
    {
        $balance = max(0, (float) $this->getOwnerRecord()->grand_total - (float) $this->getOwnerRecord()->payments()->sum('amount'));

        return $schema->components([
            Select::make('payment_method')
                ->options([
                    'cash' => 'Cash',
                    'card' => 'Card',
                    'upi' => 'UPI',
                    'room_posting' => 'Room Posting',
                    'wallet' => 'Wallet',
                ])
                ->required(),
            TextInput::make('amount')
                ->numeric()
                ->default($balance)
                ->minValue(0)
                ->required(),
            TextInput::make('transaction_reference')
                ->label('Transaction Ref'),
            DateTimePicker::make('paid_at')
                ->default(now())
                ->required(),
        ]);
    }
}
