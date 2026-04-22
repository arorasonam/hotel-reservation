<?php

namespace App\Filament\Resources\Reservations\RelationManagers;

use App\Models\HotelRoom;
use App\Models\ReservationFolio;
use App\Models\ReservationRoom;
use App\Services\ReservationFolioService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class FoliosRelationManager extends RelationManager
{
    protected static string $relationship = 'folios';

    protected static ?string $title = 'Folio';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('reservationRoom'))
            ->defaultSort('posted_at', 'desc')
            ->groups([
                Group::make('reservation_room_id')
                    ->label('Room Folio')
                    ->getTitleFromRecordUsing(fn (ReservationFolio $record): string => $record->reservationRoom
                        ? 'Room '.$record->reservationRoom->room_number.' Charges'
                        : 'Master Folio')
                    ->getDescriptionFromRecordUsing(function (ReservationFolio $record): string {
                        if (! $record->reservationRoom) {
                            return 'Reservation-level entries and unassigned payments';
                        }

                        return sprintf(
                            'Charges: %s | Credits: %s | Balance: %s',
                            number_format($record->reservationRoom->total_folio_debits, 2),
                            number_format($record->reservationRoom->total_folio_credits, 2),
                            number_format($record->reservationRoom->remaining_balance, 2),
                        );
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('reservation_room_id')
            ->columns([
                TextColumn::make('posted_at')
                    ->label('Posted')
                    ->dateTime(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('reservationRoom.room_number')
                    ->label('Room')
                    ->placeholder('Master'),
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
            ->filters([
                SelectFilter::make('reservation_room_id')
                    ->label('Room')
                    ->options(fn (): array => $this->roomOptions())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('entry_type')
                    ->label('Entry Type')
                    ->options([
                        'charge' => 'Charges',
                        'tax' => 'Taxes',
                        'discount' => 'Discounts',
                        'payment' => 'Payments',
                        'refund' => 'Refunds',
                    ]),
                SelectFilter::make('source')
                    ->options([
                        'manual' => 'Manual',
                        'reservation' => 'Reservation',
                        'reservation_room' => 'Room Stay',
                        'pos_order' => 'POS Order',
                        'pos_payment' => 'POS Payment',
                    ]),
            ])
            ->headerActions([
                // $this->makeRoomStatusAction(
                //     name: 'checkInRoom',
                //     label: 'Check In Room',
                //     color: 'success',
                //     targetStatus: 'checked_in',
                //     physicalRoomStatus: 'occupied',
                // ),
                // $this->makeRoomStatusAction(
                //     name: 'checkOutRoom',
                //     label: 'Check Out Room',
                //     color: 'gray',
                //     targetStatus: 'checked_out',
                //     physicalRoomStatus: 'dirty',
                // ),
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
                    ->label('Print All Folios')
                    ->icon('heroicon-o-printer')
                    ->url(fn (): string => route('reservations.invoice.print', $this->getOwnerRecord()))
                    ->openUrlInNewTab(),
                Action::make('printFolio')
                    ->label('Print Folio')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Select::make('folio')
                            ->label('Folio')
                            ->options(fn (): array => ['master' => 'Master Folio'] + $this->roomOptions())
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data): void {
                        $url = $data['folio'] === 'master'
                            ? route('reservations.folios.master.print', $this->getOwnerRecord())
                            : route('reservations.folios.room.print', [
                                'reservation' => $this->getOwnerRecord(),
                                'reservationRoom' => $data['folio'],
                            ]);

                        $this->js("window.open('{$url}', '_blank')");
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn (ReservationFolio $record): bool => $record->source === 'manual'),
            ])
            ->bulkActions([]);
    }

    private function makeRoomStatusAction(
        string $name,
        string $label,
        string $color,
        string $targetStatus,
        string $physicalRoomStatus,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->color($color)
            ->form([
                Select::make('reservation_room_id')
                    ->label('Room')
                    ->options(fn (): array => $this->roomOptions($targetStatus === 'checked_in' ? 'pending' : 'checked_in'))
                    ->required()
                    ->searchable()
                    ->preload(),
            ])
            ->requiresConfirmation()
            ->action(function (array $data) use ($targetStatus, $physicalRoomStatus): void {
                $reservationRoom = $this->getOwnerRecord()
                    ->reservationRooms()
                    ->find($data['reservation_room_id']);

                if (! $reservationRoom) {
                    return;
                }

                if ($targetStatus === 'checked_out' && $reservationRoom->remaining_balance > 0) {
                    Notification::make()
                        ->title('Room has pending balance')
                        ->body('Please settle the room folio before checkout.')
                        ->danger()
                        ->send();

                    return;
                }

                $reservationRoom->forceFill([
                    'status' => $targetStatus,
                    'checked_in_at' => $targetStatus === 'checked_in' ? now() : $reservationRoom->checked_in_at,
                    'checked_out_at' => $targetStatus === 'checked_out' ? now() : $reservationRoom->checked_out_at,
                ])->save();

                $this->markPhysicalRoom($reservationRoom, $physicalRoomStatus);

                if ($targetStatus === 'checked_in') {
                    app(ReservationFolioService::class)->syncReservationRoomStayCharge($reservationRoom->refresh());
                }

                Notification::make()
                    ->title($targetStatus === 'checked_in' ? 'Room checked in' : 'Room checked out')
                    ->success()
                    ->send();
            });
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
                Select::make('reservation_room_id')
                    ->label('Room Folio')
                    ->options(fn (): array => $this->roomOptions())
                    ->searchable()
                    ->preload(),
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
                    'reservation_room_id' => $data['reservation_room_id'] ?? null,
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

    private function roomOptions(?string $status = null): array
    {
        return $this->getOwnerRecord()
            ->reservationRooms()
            ->when($status === 'pending', fn ($query) => $query->whereNotIn('status', ['checked_in', 'checked_out']))
            ->when($status && $status !== 'pending', fn ($query) => $query->where('status', $status))
            ->get()
            ->mapWithKeys(fn (ReservationRoom $roomStay) => [
                $roomStay->id => $roomStay->display_name,
            ])
            ->toArray();
    }

    private function markPhysicalRoom(ReservationRoom $reservationRoom, string $status): void
    {
        if (! $reservationRoom->room_number || $reservationRoom->room_number === 'Auto') {
            return;
        }

        HotelRoom::query()
            ->where('room_number', $reservationRoom->room_number)
            ->update(['status' => $status]);
    }
}
