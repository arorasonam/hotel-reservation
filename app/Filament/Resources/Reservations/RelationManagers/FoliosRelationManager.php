<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Models\ReservationFolio;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FoliosRelationManager extends RelationManager
{
    protected static string $relationship = 'folios';

    protected static ?string $title = 'Folio';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('posted_at', 'desc')
            ->columns([
                TextColumn::make('posted_at')
                    ->label('Posted')
                    ->dateTime(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('entry_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'charge' => 'gray',
                        'tax' => 'warning',
                        'discount' => 'info',
                        'payment' => 'success',
                        'refund' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reference')
                    ->toggleable(),
                TextColumn::make('source')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('debit')
                    ->state(fn (ReservationFolio $record): ?float => $record->type === 'debit' ? (float) $record->amount : null)
                    ->money('INR'),
                TextColumn::make('credit')
                    ->state(fn (ReservationFolio $record): ?float => $record->type === 'credit' ? (float) $record->amount : null)
                    ->money('INR'),
            ])
            ->headerActions([
                $this->makeEntryAction(
                    name: 'addCharge',
                    label: 'Add Charge',
                    color: 'gray',
                    entryType: 'charge',
                    type: 'debit',
                    descriptionPlaceholder: 'Extra room service, minibar, laundry',
                ),
                $this->makeEntryAction(
                    name: 'addTax',
                    label: 'Add Tax',
                    color: 'warning',
                    entryType: 'tax',
                    type: 'debit',
                    descriptionPlaceholder: 'GST or other tax adjustment',
                ),
                $this->makeEntryAction(
                    name: 'addDiscount',
                    label: 'Add Discount',
                    color: 'info',
                    entryType: 'discount',
                    type: 'credit',
                    descriptionPlaceholder: 'Promotional or goodwill discount',
                ),
                $this->makeEntryAction(
                    name: 'addPayment',
                    label: 'Add Payment',
                    color: 'success',
                    entryType: 'payment',
                    type: 'credit',
                    descriptionPlaceholder: 'Cash, card, bank transfer',
                ),
                $this->makeEntryAction(
                    name: 'addRefund',
                    label: 'Add Refund',
                    color: 'danger',
                    entryType: 'refund',
                    type: 'debit',
                    descriptionPlaceholder: 'Guest refund or payout',
                ),
                Action::make('printInvoice')
                    ->label('Print Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn (): string => route('reservations.invoice.print', $this->getOwnerRecord()))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn (ReservationFolio $record): bool => $record->source === 'manual'),
            ])
            ->bulkActions([]);
    }

    private function makeEntryAction(
        string $name,
        string $label,
        string $color,
        string $entryType,
        string $type,
        string $descriptionPlaceholder,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->color($color)
            ->form([
                TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->placeholder($descriptionPlaceholder),
                TextInput::make('reference')
                    ->maxLength(255),
                TextInput::make('amount')
                    ->numeric()
                    ->minValue(0.01)
                    ->required(),
                DateTimePicker::make('posted_at')
                    ->default(now())
                    ->required(),
                Textarea::make('notes')
                    ->rows(3),
            ])
            ->action(function (array $data) use ($entryType, $type): void {
                $this->getOwnerRecord()->folios()->create([
                    'source' => 'manual',
                    'source_id' => null,
                    'source_key' => $entryType,
                    'description' => $data['description'],
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'amount' => $data['amount'],
                    'type' => $type,
                    'entry_type' => $entryType,
                    'posted_at' => $data['posted_at'],
                ]);
            });
    }
}
