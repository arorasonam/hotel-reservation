<?php

namespace App\Filament\Resources\PosOrders\RelationManagers;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
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
                TextColumn::make('currency_code')
                    ->label('Currency'),
                TextColumn::make('amount')
                    ->money(fn ($record): string => $record->currency_code ?? 'INR'),
                // TextColumn::make('base_amount')
                //     ->money('INR'),
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
                        $order = $this->getOwnerRecord();
                        $currencyCode = $data['currency_code'] ?? $order->currency_code ?? 'INR';
                        $rate = (float) ($data['exchange_rate_used'] ?? $order->exchange_rate_used ?? app(CurrencyService::class)->getRate($currencyCode));
                        $amount = (float) ($data['amount'] ?? 0);

                        $data['pos_order_id'] = $order->id;
                        $data['reservation_id'] = $order->reservation_id;
                        $data['reservation_room_id'] = $order->reservation_room_id;
                        $data['reservation_room_detail_id'] = $order->reservation_room_detail_id;
                        $data['currency_code'] = $currencyCode;
                        $data['exchange_rate_used'] = $rate;
                        $data['base_amount'] = app(CurrencyService::class)->toBase($amount, $rate);
                        $data['received_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([])
            ->bulkActions([])
            ->defaultSort('id', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        $order = $this->getOwnerRecord();
        $currencyCode = $order->currency_code ?? 'INR';
        $exchangeRate = (float) ($order->exchange_rate_used ?? app(CurrencyService::class)->getRate($currencyCode));
        $balance = max(0, (float) $order->grand_total - (float) $order->payments()->sum('amount'));

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
            Select::make('currency_code')
                ->label('Currency')
                ->options(Currency::pluck('code', 'code'))
                ->default($currencyCode)
                ->disabled()
                ->dehydrated()
                ->required(),
            TextInput::make('exchange_rate_used')
                ->numeric()
                ->default($exchangeRate)
                ->disabled()
                ->dehydrated()
                ->required(),
            TextInput::make('amount')
                ->numeric()
                ->default($balance)
                ->minValue(0)
                ->required(),
            Hidden::make('base_amount')
                ->default(app(CurrencyService::class)->toBase($balance, $exchangeRate))
                ->dehydrated(),
            TextInput::make('transaction_reference')
                ->label('Transaction Ref'),
            DateTimePicker::make('paid_at')
                ->default(now())
                ->required(),
        ]);
    }
}
