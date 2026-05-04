<?php

namespace App\Filament\Resources\PosOrders\RelationManagers;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Support\CurrencyDefaults;
use App\Support\MoneyConverter;
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
                    ->formatStateUsing(fn ($state, $record): string => ($record->currency_code ?? CurrencyDefaults::defaultCode()).' '.number_format((float) $state, 2)),
                TextColumn::make('base_amount')
                    ->label('Base Amount')
                    ->formatStateUsing(fn ($state, $record): string => ($record->base_currency_code ?? CurrencyDefaults::defaultCode()).' '.number_format((float) $state, 2)),
                TextColumn::make('exchange_rate')
                    ->label('Rate'),
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
                        $data['reservation_room_id'] = $this->getOwnerRecord()->reservation_room_id;
                        $data['reservation_room_detail_id'] = $this->getOwnerRecord()->reservation_room_detail_id;
                        $data['currency_code'] = $data['currency_code'] ?? $this->getOwnerRecord()->currency_code ?? CurrencyDefaults::defaultCode();
                        $data['base_currency_code'] = $this->getOwnerRecord()->base_currency_code ?? MoneyConverter::baseCurrencyForHotel($this->getOwnerRecord()->hotel_id);
                        $data['exchange_rate'] = $data['exchange_rate'] ?? $this->getOwnerRecord()->exchange_rate ?? MoneyConverter::exchangeRateToBase($data['currency_code'], $data['base_currency_code']);
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
                ->prefix($this->getOwnerRecord()->currency_code ?? CurrencyDefaults::defaultCode())
                ->default($balance)
                ->minValue(0)
                ->required(),
            TextInput::make('exchange_rate')
                ->hidden()
                ->default($this->getOwnerRecord()->exchange_rate ?? CurrencyDefaults::defaultExchangeRate())
                ->dehydrated(true),
            TextInput::make('transaction_reference')
                ->label('Transaction Ref'),
            DateTimePicker::make('paid_at')
                ->default(now())
                ->required(),
        ]);
    }
}
