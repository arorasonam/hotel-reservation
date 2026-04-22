<?php

namespace App\Filament\Resources\PosOrders;

use App\Filament\Resources\PosOrders\Pages\CreatePosOrder;
use App\Filament\Resources\PosOrders\Pages\EditPosOrder;
use App\Filament\Resources\PosOrders\Pages\ListPosOrders;
use App\Models\HotelRoom;
use App\Models\PosCategory;
use App\Models\PosItem;
use App\Models\PosOrder;
use App\Models\PosOutlet;
use App\Models\ReservationRoom;
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

class PosOrderResource extends Resource
{
    protected static ?string $model = PosOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'POS Order';

    protected static ?int $navigationSort = 4;

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
                    ->required(),
                TextInput::make('table_no')
                    ->label('Table No')
                    ->maxLength(50)
                    ->visible(fn ($get) => $get('order_type') !== 'takeaway'),
                Hidden::make('reservation_id')
                    ->dehydrated(true),
                Select::make('reservation_room_id')
                    ->label('Room Posting')
                    ->getSearchResultsUsing(function (string $search) {
                        return ReservationRoom::query()
                            // ->where('status', 'checked_in')
                            ->whereHas('reservation')
                            ->where(function ($query) use ($search) {
                                $query->where('room_number', 'like', "%{$search}%")
                                    ->orWhereHas('reservation', function ($reservationQuery) use ($search) {
                                        $reservationQuery->where('reservation_number', 'like', "%{$search}%")
                                            ->orWhereHas('reservationGuests', function ($guestQuery) use ($search) {
                                                $guestQuery->where('first_name', 'like', "%{$search}%")
                                                    ->orWhere('last_name', 'like', "%{$search}%");
                                            });
                                    });
                            })
                            ->with(['reservation.reservationGuests'])
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (ReservationRoom $roomStay) => [
                                $roomStay->id => $roomStay->display_name,
                            ]);
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => ReservationRoom::with('reservation.reservationGuests')->find($value)?->display_name)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $roomStay = ReservationRoom::with('reservation')->find($state);

                        if (! $roomStay) {
                            $set('reservation_id', null);
                            $set('room_id', null);
                            $set('guest_id', null);

                            return;
                        }

                        $room = HotelRoom::query()
                            ->where('room_number', $roomStay->room_number)
                            ->where('hotel_id', $roomStay->reservation?->hotel_id)
                            ->first();

                        if ($room) {
                            $set('room_id', $room->id);
                        }

                        $set('reservation_id', $roomStay->reservation_id);
                        $set('guest_id', $roomStay->reservation?->guest_id);
                    })
                    ->searchable()
                    ->reactive()
                    ->visible(fn ($get) => $get('order_type') === 'room_charge')
                    ->required(fn ($get) => $get('order_type') === 'room_charge'),
                Select::make('room_id')
                    ->relationship('room', 'room_number')
                    ->disabled(fn ($get) => $get('order_type') === 'room_charge')
                    ->dehydrated(),
                Select::make('guest_id')
                    ->relationship('guest', 'first_name')
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

                                if (! $state) return;

                                $item = \App\Models\PosItem::find($state);

                                if (! $item) return;

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

                        if (!empty($data['tax_id'])) {
                            $tax = \App\Models\Tax::find($data['tax_id']);
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
                TextColumn::make('reservationRoom.room_number')
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

                        app(ReservationFolioService::class)->syncPosOrderCharges($record->fresh(['reservation', 'reservationRoom']));
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

        if ($grandTotal <= 0) {
            $data['status'] = 'paid';
            $data['settled_at'] = now();
        }

        return $data;
    }
}
