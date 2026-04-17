<?php

namespace App\Filament\Resources\PosOrders\RelationManagers;

use App\Filament\Resources\PosOrders\PosOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'Payments';

    protected static ?string $title = 'Payments';

    protected static ?string $relatedResource = PosOrderResource::class;
    

    public function table(Table $table): Table
    {
        return $table->columns([

            TextColumn::make('payment_method'),

            TextColumn::make('amount')
                ->money('INR'),

            TextColumn::make('transaction_reference'),

            TextColumn::make('paid_at')
                ->dateTime(),

        ]) ->headerActions([

            CreateAction::make('payment')
                ->label('Generate Payment')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->mutateFormDataUsing(function ($data) {
                    $data['order_id'] = $this->getOwnerRecord()->id;
                    $data['received_by'] = Auth::id();

                    return $data;
                }),

        ])->recordActions([])
            ->bulkActions([]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('payment_method')
                ->options([
                    'cash'  => 'Cash',
                    'card'  => 'Card',
                    'upi'   => 'UPI',
                    'room_posting' => 'Room Posting',
                    'wallet'=> 'Wallet',
                ])
                ->required(),

            TextInput::make('amount')
                ->numeric()
                ->required(),

            TextInput::make('transaction_reference')
                ->label('Transaction Ref'),

            DateTimePicker::make('paid_at')
                ->default(now())
                ->required(),

        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['received_by'] = auth()->id();

        return $data;
    }
}
