<?php

namespace App\Filament\Resources\PosOrders;

use App\Filament\Resources\PosOrders\Pages\CreatePosOrder;
use App\Filament\Resources\PosOrders\Pages\EditPosOrder;
use App\Filament\Resources\PosOrders\Pages\ListPosOrders;
use App\Helpers\HotelContext;
use App\Models\HotelRoom;
use App\Models\PosCategory;
use App\Models\PosItem;
use App\Models\PosOrder;
use App\Models\PosOutlet;
use App\Models\Reservation;
use App\Models\ReservationRoomDetail;
use App\Models\Tax;
use App\Services\ReservationFolioService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class PosOrderResource extends Resource
{
    protected static ?string $model = PosOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'POS Order';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (HotelContext::isFiltering()) {
            $query->where('hotel_id', HotelContext::selectedId());
        }

        $user = auth()->user();
        // If bartender, show related outlet data //
        if ($user->hasRole('bartender')) {
            $query->where('pos_outlet_id', $user->pos_outlet_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('hotel_id')->dehydrated(true),
                TextInput::make('order_number')
                    ->default(fn () => 'POS-'.now()->format('YmdHisv'))
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Select::make('pos_outlet_id')
                    ->label('Outlet')
                    ->default(fn () => auth()->user()?->pos_outlet_id)
                    ->disabled(fn () => filled(auth()->user()?->pos_outlet_id))
                    ->dehydrated()
                    ->live()
                    ->options(function (): array {
                        $query = PosOutlet::query()->where('status', true);
                        $userOutletId = auth()->user()?->pos_outlet_id;

                        if ($userOutletId) {
                            $query->whereKey($userOutletId);
                        }

                        return $query->orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->afterStateUpdated(function ($state, $set): void {
                        $outlet = PosOutlet::find($state);

                        if ($outlet) {
                            $set('hotel_id', $outlet->hotel_id);
                        }
                    })
                    ->afterStateHydrated(function ($state, $set): void {
                        if (! $state) {
                            return;
                        }

                        $outlet = PosOutlet::find($state);

                        if ($outlet) {
                            $set('hotel_id', $outlet->hotel_id);
                        }
                    })
                    ->required(),
                Select::make('order_type')
                    ->options([
                        'room_charge' => 'Room Charge',
                        'walk_in' => 'Walk-in',
                        'takeaway' => 'Takeaway',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state === 'room_charge') {
                            return;
                        }

                        $set('reservation_room_detail_id', null);
                        $set('reservation_room_id', null);
                        $set('reservation_id', null);
                        $set('guest_id', null);
                    })
                    ->required(),
                TextInput::make('table_no')
                    ->label('Table No')
                    ->maxLength(50)
                    ->visible(fn ($get) => $get('order_type') !== 'takeaway'),
                Hidden::make('reservation_id')
                    ->dehydrated(true),
                Hidden::make('reservation_room_id')
                    ->dehydrated(true),

                Select::make('reservation_room_detail_id')
                    ->label('Room Posting')
                    ->searchable()
                    ->live()
                    ->visible(fn ($get): bool => $get('order_type') === 'room_charge')
                    ->required(fn ($get): bool => $get('order_type') === 'room_charge')
                    ->getSearchResultsUsing(function (string $search) {
                        return ReservationRoomDetail::query()
                            ->where('status', 'checked_in')
                            ->whereHas('category.reservation')
                            ->where(function ($query) use ($search) {
                                $query->where('room_number', 'like', "%{$search}%")
                                    ->orWhereHas('category.reservation', function ($reservationQuery) use ($search) {
                                        $reservationQuery->where('reservation_number', 'like', "%{$search}%")
                                            ->orWhereHas('reservationGuests', function ($guestQuery) use ($search) {
                                                $guestQuery->where('first_name', 'like', "%{$search}%")
                                                    ->orWhere('last_name', 'like', "%{$search}%");
                                            });
                                    });
                            })
                            ->with(['category.reservation.reservationGuests'])
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function (ReservationRoomDetail $detail) {
                                $reservation = $detail->category?->reservation;

                                return [
                                    $detail->id => 'Room '.$detail->room_number.' - '.($reservation?->reservation_number ?? ''),
                                ];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $detail = ReservationRoomDetail::with('category.reservation.reservationGuests')
                            ->find($value);

                        if (! $detail) {
                            return null;
                        }

                        $reservation = $detail->category?->reservation;

                        return $detail->display_name ?? (
                            $detail->room_number.' - '.($reservation?->reservation_number ?? '')
                        );
                    })
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $postingData = self::getRoomPostingData($state);

                        $set('reservation_id', $postingData['reservation_id']);
                        $set('reservation_room_id', $postingData['reservation_room_id']);
                        $set('room_id', $postingData['room_id']);
                        $set('guest_id', $postingData['guest_id']);
                    }),

                Select::make('room_id')
                    ->relationship('room', 'room_number')
                    ->disabled(fn ($get) => $get('order_type') === 'room_charge')
                    ->dehydrated(),
                Select::make('guest_id')
                    ->relationship('guest', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => trim("{$record->first_name} {$record->last_name}"))
                    ->disabled(fn ($get) => $get('order_type') === 'room_charge')
                    ->searchable()
                    ->dehydrated(),
                TextInput::make('discount_amount')
                    ->numeric()
                    ->default(0)
                    ->label('Discount'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'paid' => 'Paid',
                    ])
                    ->default('draft')
                    ->required(),

                Repeater::make('items')
                    ->relationship()
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('tax_id')
                            ->dehydrated(true),
                        Select::make('pos_category_id')
                            ->label('Category')
                            // ->dehydrated(false)
                            ->options(function ($livewire): array {
                                $outletId = data_get($livewire->data, 'pos_outlet_id');

                                if (! $outletId) {
                                    return [];
                                }

                                return PosCategory::query()
                                    ->where('pos_outlet_id', $outletId)
                                    ->where('status', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function (callable $set): void {
                                $set('pos_item_id', null);
                                $set('tax_id', null);
                                $set('tax_percentage', 0);
                                $set('price', 0);
                                $set('subtotal', 0);
                                $set('tax_amount', 0);
                                $set('total', 0);
                            }),
                        Select::make('pos_item_id')
                            ->label('Item')
                            // ->relationship('item', 'name')
                            ->options(function ($livewire, callable $get): array {
                                $outletId = data_get($livewire->data, 'pos_outlet_id');
                                $categoryId = $get('pos_category_id');

                                if (! $outletId || ! $categoryId) {
                                    return [];
                                }

                                return PosItem::query()
                                    ->where('pos_outlet_id', $outletId)
                                    ->where('pos_category_id', $categoryId)
                                    ->where('status', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->disabled(fn ($get, $livewire) => empty(data_get($livewire->data, 'pos_outlet_id')) || empty($get('pos_category_id')))
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                $item = PosItem::find($state);

                                if (! $item) {
                                    return;
                                }

                                $tax = $item->category?->tax;
                                $taxId = $tax?->id;
                                $price = $item->price;
                                $qty = $get('quantity') ?? 1;
                                $taxPercent = $tax?->percentage ?? 0;

                                $subtotal = $price * $qty;
                                $taxAmount = ($subtotal * $taxPercent) / 100;
                                $total = $subtotal + $taxAmount;

                                $set('tax_id', $taxId);
                                $set('price', $price);
                                $set('tax_percentage', $taxPercent);
                                $set('subtotal', $subtotal);
                                $set('tax_amount', $taxAmount);
                                $set('total', $total);
                            })
                            ->afterStateHydrated(function ($state, callable $set, callable $get) {

                                if (! $state) {
                                    return;
                                }

                                $item = PosItem::find($state);

                                if (! $item) {
                                    return;
                                }

                                $tax = $item->category?->tax;
                                $taxPercent = $tax?->percentage ?? 0;
                                $price = $item->price;
                                $qty = $get('quantity') ?? 1;

                                $subtotal = $price * $qty;
                                $taxAmount = ($subtotal * $taxPercent) / 100;
                                $total = $subtotal + $taxAmount;

                                $set('tax_id', $tax?->id);
                                $set('price', $price);
                                $set('tax_percentage', $taxPercent);
                                $set('subtotal', $subtotal);
                                $set('tax_amount', $taxAmount);
                                $set('total', $total);
                            })
                            ->required(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                $price = $get('price') ?? 0;
                                $taxPercent = $get('tax_percentage') ?? 0;
                                $subtotal = $price * $state;
                                $taxAmount = ($subtotal * $taxPercent) / 100;
                                $total = $subtotal + $taxAmount;

                                $set('subtotal', $subtotal);
                                $set('tax_amount', $taxAmount);
                                $set('total', $total);
                            })
                            ->required(),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(true),
                        Placeholder::make('applied_tax')
                            ->label('Applied Tax')
                            ->content(fn ($get) => ($get('tax_percentage') ?? 0).'%'),
                        TextInput::make('tax_percentage')
                            ->hidden()
                            ->reactive()
                            ->dehydrated(true),
                        TextInput::make('subtotal')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                        TextInput::make('tax_amount')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                        TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {

                        $price = $data['price'] ?? 0;
                        $qty = $data['quantity'] ?? 1;

                        if (! empty($data['tax_id'])) {
                            $tax = Tax::find($data['tax_id']);
                            $taxPercent = $tax?->percentage ?? 0;
                        } else {
                            $taxPercent = 0;
                        }

                        $subtotal = $price * $qty;
                        $taxAmount = ($subtotal * $taxPercent) / 100;

                        $data['tax_percentage'] = $taxPercent;
                        $data['tax_amount'] = $taxAmount;
                        $data['total'] = $subtotal + $taxAmount;

                        return $data;
                    })
                    ->columns(8)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation.reservation_number')
                    ->label('Reservation'),
                TextColumn::make('reservationRoomDetail.room_number')
                    ->label('Stay Room')
                    ->placeholder('N/A'),
                TextColumn::make('room.room_number')
                    ->label('Room No'),
                TextColumn::make('table_no')
                    ->label('Table No')
                    ->placeholder('N/A'),
                TextColumn::make('reservation.reservationGuests.first_name')
                    ->label('First Name')
                    ->placeholder('First Name'),
                TextColumn::make('subtotal')
                    ->money('INR'),
                TextColumn::make('tax_amount')
                    ->money('INR'),
                TextColumn::make('discount_amount')
                    ->money('INR'),
                TextColumn::make('grand_total')
                    ->money('INR'),
                TextColumn::make('settled_at')
                    ->dateTime()
                    ->placeholder('Pending'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'draft' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Open',
                        'confirmed' => 'Pending Settlement',
                        'paid' => 'Settled',
                        'cancelled' => 'Cancelled',
                    ]),
                Filter::make('today_orders')
                    ->label("Today's Orders")
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('today_settled')
                    ->label("Today's Settled")
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', 'paid')
                        ->whereDate('settled_at', today())),
                Filter::make('pending_settlement')
                    ->label('Pending Settlement')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'confirmed')),
                Filter::make('open_orders')
                    ->label('Open')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['draft', 'confirmed'])),
            ])
            ->recordActions([
                Action::make('confirmOrder')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function (PosOrder $record): void {
                        $record->update([
                            'status' => (float) $record->grand_total <= 0 ? 'paid' : 'confirmed',
                            'settled_at' => (float) $record->grand_total <= 0 ? now() : null,
                        ]);

                        app(ReservationFolioService::class)->syncPosOrderCharges($record->fresh(['reservation', 'reservationRoomDetail']));
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('print_invoice')
                    ->label('Print Bill')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('pos.invoice.print', $record->id))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosOrders::route('/'),
            'create' => CreatePosOrder::route('/create'),
            'edit' => EditPosOrder::route('/{record}/edit'),
            'view' => Pages\ViewPosOrder::route('/{record}'),
        ];
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        return self::prepareCreateData($data);
    }

    public static function prepareCreateData(array $data): array
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($data['items'] as &$item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $itemTax = (float) ($item['tax_amount'] ?? 0);
            $item['total'] = $itemSubtotal + $itemTax;
            $item['subtotal'] = $itemSubtotal;
            $subtotal += $itemSubtotal;
            $taxAmount += $itemTax;
        }

        $discount = (float) ($data['discount_amount'] ?? 0);
        $grandTotal = $subtotal + $taxAmount - $discount;

        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = $taxAmount;
        $data['grand_total'] = $grandTotal;
        $data['created_by'] = auth()->id();

        if (($data['order_type'] ?? null) !== 'room_charge') {
            $data['reservation_id'] = null;
            $data['reservation_room_id'] = null;
            $data['reservation_room_detail_id'] = null;
            $data['guest_id'] = null;
            $data['room_id'] = null;
        } else {
            $data = array_merge($data, self::getRoomPostingData($data['reservation_room_detail_id'] ?? null));
        }

        if ($grandTotal <= 0) {
            $data['status'] = 'paid';
            $data['settled_at'] = now();
        }

        return $data;
    }

    /**
     * @return array{reservation_id: int|null, reservation_room_id: int|null, room_id: string|null, guest_id: int|null}
     */
    private static function getRoomPostingData(mixed $reservationRoomDetailId): array
    {
        $emptyPostingData = [
            'reservation_id' => null,
            'reservation_room_id' => null,
            'room_id' => null,
            'guest_id' => null,
        ];

        if (! $reservationRoomDetailId) {
            return $emptyPostingData;
        }

        $detail = ReservationRoomDetail::with('category.reservation.reservationGuests')
            ->find($reservationRoomDetailId);

        if (! $detail) {
            return $emptyPostingData;
        }

        $reservation = $detail->category?->reservation;
        $roomId = HotelRoom::query()
            ->where('room_number', $detail->room_number)
            ->when($reservation?->hotel_id, fn (Builder $query, string $hotelId): Builder => $query->where('hotel_id', $hotelId))
            ->value('id');

        return [
            'reservation_id' => $reservation?->id,
            'reservation_room_id' => null,
            'room_id' => $roomId,
            'guest_id' => self::getReservationGuestId($reservation),
        ];
    }

    private static function getReservationGuestId(?Reservation $reservation): ?int
    {
        if (! $reservation) {
            return null;
        }

        $reservationGuest = $reservation->reservationGuests
            ->whereNotNull('guest_id')
            ->sortByDesc('is_primary')
            ->first();

        return $reservationGuest?->guest_id ?? $reservation->guest_id;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('order_number')
                                ->label('Order No'),

                            TextEntry::make('outlet.name')
                                ->label('Outlet'),

                            TextEntry::make('order_type')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'room_charge' => 'info',
                                    'walk_in' => 'success',
                                    'takeaway' => 'warning',
                                }),

                            TextEntry::make('table_no')
                                ->label('Table No')
                                ->visible(fn ($record) => $record->order_type !== 'takeaway'),

                            TextEntry::make('room.room_number')
                                ->label('Room'),

                            TextEntry::make('guest.name')
                                ->label('Guest'),
                        ]),
                    ]),

                Section::make('Financial')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('discount_amount')
                                ->money('INR'),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'draft' => 'gray',
                                    'confirmed' => 'info',
                                    'paid' => 'success',
                                    'cancelled' => 'danger',
                                }),

                            TextEntry::make('created_at')
                                ->dateTime(),

                            TextEntry::make('updated_at')
                                ->dateTime(),
                        ]),
                    ]),

                Section::make('Order Items')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Grid::make(8)->schema([
                                    TextEntry::make('category.name')
                                        ->label('Category'),

                                    TextEntry::make('item.name')
                                        ->label('Item'),

                                    TextEntry::make('quantity'),

                                    TextEntry::make('price')
                                        ->money('INR'),

                                    TextEntry::make('tax_percentage')
                                        ->suffix('%'),

                                    TextEntry::make('subtotal')
                                        ->money('INR'),

                                    TextEntry::make('tax_amount')
                                        ->money('INR'),

                                    TextEntry::make('total')
                                        ->money('INR')
                                        ->weight('bold'),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
