<?php

namespace App\Filament\Resources\PosOrders;

use App\Filament\Resources\PosOrders\Pages\CreatePosOrder;
use App\Filament\Resources\PosOrders\Pages\EditPosOrder;
use App\Filament\Resources\PosOrders\Pages\ListPosOrders;
use App\Filament\Resources\PosOrders\Schemas\PosOrderForm;
use App\Filament\Resources\PosOrders\Tables\PosOrdersTable;
use App\Models\PosOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
use Filament\Forms\Components\Repeater;
use App\Models\Reservation;
use Filament\Resources\Pages\CreateRecord;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Models\PosItem;
use Filament\Forms\Components\Hidden;

class PosOrderResource extends Resource
{
    protected static ?string $model = PosOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'POS Order';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('hotel_id')->dehydrated(true),

                TextInput::make('order_number')
                    ->default(fn() => 'POS-' . now()->format('YmdHisv'))
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Select::make('pos_outlet_id')
                    ->relationship('outlet', 'name')
                    ->live()
                     ->afterStateUpdated(function ($state, $set) {

                        $outlet = \App\Models\PosOutlet::find($state);
                       
                        if ($outlet) {
                            $set('hotel_id', $outlet->hotel_id);
                        }
                        
                    })->afterStateHydrated(function ($state, $set) {

                        if ($state) {

                            $outlet = \App\Models\PosOutlet::find($state);

                            if ($outlet) {
                                $set('hotel_id', $outlet->hotel_id);
                            }
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

                Select::make('reservation_id')
                    ->relationship(
                        name: 'reservation',
                        titleAttribute: 'id',
                        modifyQueryUsing: fn($query) =>
                        $query->where('status', 'confirmed')
                    )
                    ->searchable(['id', 'reservation_number'])
                    ->getSearchResultsUsing(function (string $search) {

                        return Reservation::query()
                            ->where('status', 'confirmed')
                            ->whereNotNull('reservation_number')
                            ->where(function ($query) use ($search) {

                                $query->where('reservation_number', 'like', "%{$search}%")
                                    ->orWhere('room_no', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                                // ->orWhereHas('guest', fn ($q) =>
                                //         $q->where('first_name', 'like', "%{$search}%")
                                //         ->orWhere('last_name', 'like', "%{$search}%")
                                // );

                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn($record) => [
                                $record->id =>
                                "Room {$record->room_no} - {$record->first_name} {$record->last_name} - #{$record->reservation_number}"
                            ]);
                    })
                    ->reactive()
                    ->getOptionLabelUsing(function ($value): ?string {

                        $reservation = Reservation::find($value);

                        if (!$reservation) {
                            return null;
                        }

                        return "Room {$reservation->room_no} - {$reservation->first_name} {$reservation->last_name} - #{$reservation->reservation_number}";
                    })
                    ->afterStateUpdated(function ($state, callable $set) {

                        $reservation = \App\Models\Reservation::find($state);

                        if ($reservation) {
                            // Convert room_no → room_id
                            $room = \App\Models\HotelRoom::where(
                                'room_number',
                                $reservation->room_no
                            )->first();

                            if ($room) {
                                $set('room_id', $room->id);
                            }
                            $set('guest_id', $reservation->guest_id);
                        }
                    })
                    ->visible(fn($get) => $get('order_type') === 'room_charge')
                    ->required(fn($get) => $get('order_type') === 'room_charge'),

                Select::make('room_id')
                    ->relationship('room', 'room_number')
                    ->disabled(fn($get) => $get('order_type') === 'room_charge')
                    ->dehydrated(),

                Select::make('guest_id')
                    ->relationship('guest', 'first_name')
                    ->disabled(fn($get) => $get('order_type') === 'room_charge')
                    ->searchable()
                    ->dehydrated(),

                TextInput::make('discount_amount')
                    ->numeric()
                    ->default(0)
                    ->label('Discount'),

                Repeater::make('items')
                    ->relationship()
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('tax_id')
                        ->dehydrated(true),

                        Select::make('pos_item_id')
                            ->relationship('item', 'name')
                            ->options(function ($livewire) {

                                $outletId = data_get($livewire->data, 'pos_outlet_id');

                                if (!$outletId) {
                                    return [];
                                }

                                return PosItem::where('pos_outlet_id', $outletId)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->disabled(fn ($livewire) => empty(data_get($livewire->data, 'pos_outlet_id')))
                             ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                $item = \App\Models\PosItem::find($state);

                                if (!$item) {
                                    return;
                                }

                               
                                $taxId = $item->tax->id ?? null;
                                $price = $item->price;
                                $qty   = $get('quantity') ?? 1;
                                $taxPercent = $item->tax->percentage ?? 0;

                                $subtotal  = $price * $qty;
                                $taxAmount = ($subtotal * $taxPercent) / 100;
                                $total = $subtotal + $taxAmount;

                                $set('tax_id', $taxId);
                                $set('price', $price);
                                $set('tax_percentage', $taxPercent);
                                $set('subtotal', $subtotal);
                                $set('tax_amount', $taxAmount);
                                $set('total', $total);

                                // if ($item->tax) {

                                //     $set('tax_amount', $item->tax->percentage);

                                // }
                                // $quantity =$item->quantity ?? 1;

                                // $set('total', $item->price * $quantity);
                            })
                            ->required(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
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
                            ->reactive()
                            ->required()
                            ->disabled()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {

                                $qty = $get('quantity') ?? 1;
                                $taxPercent = $get('tax_percentage') ?? 0;

                                $subtotal = $state * $qty;
                                $taxAmount = ($subtotal * $taxPercent) / 100;
                                $total = $subtotal + $taxAmount;
                                dd($taxAmount);
                                $set('subtotal', $subtotal);
                                $set('tax_amount', $taxAmount);
                                $set('total', $total);
                            })
                            ->dehydrated(true),
                        
                        TextInput::make('tax_percentage')
                            ->numeric()
                            ->disabled()
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
                    ->columns(7)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation.reservation_number')
                    ->label('Reservation'),

                TextColumn::make('room.room_number')
                    ->label('Room No'),

                TextColumn::make('reservation.first_name')
                    ->label('First Name'),

                TextColumn::make('payments_sum_amount')
                    ->sum('payments', 'amount')
                    ->label('Paid Amount')
                    ->money('INR'),

                TextColumn::make('subtotal')
                    ->money('INR'),

                TextColumn::make('tax_amount')
                    ->money('INR'),

                TextColumn::make('discount_amount')
                    ->money('INR'),
                    // ->maxValue(fn ($get) => $get('subtotal')),

                TextColumn::make('grand_total')
                    ->money('INR'),
                
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'draft' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

            ])->recordActions([
               
                Action::make('confirmOrder')
                ->label('Confirm')
                ->icon('heroicon-o-check-circle')
                ->visible(fn ($record) => $record->status === 'draft')
                ->action(function ($record) {

                    $record->update([
                        'status' => 'confirmed'
                    ]);

                }),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

                Action::make('print_invoice')
                    ->label('Print Bill')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) =>
                        route('pos.invoice.print', $record->id)
                    )
                    ->openUrlInNewTab(),

                // Action::make('download_invoice')
                //     ->label('Download PDF')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->url(fn ($record) =>
                //         route('pos.invoice.download', $record->id)
                //     )
                //     ->openUrlInNewTab()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
            // RelationManagers\ItemsRelationManager::class,
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

    protected function afterCreate(): void
    {
        $subtotal = 0;

        foreach ($this->record->items as $item) {
            $subtotal += ($item->price * $item->quantity);
        }

        $tax = $this->record->tax ?? 0;
        $discount = $this->record->discount ?? 0;

        $grandTotal = ($subtotal + $tax) - $discount;

        $this->record->update([
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ]);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $subtotal = 0;

        $taxAmount = 0;

        foreach ($data['items'] as &$item) {

            $itemSubtotal = $item['price'] * $item['quantity'];

            $itemTax = ($itemSubtotal * $item['tax']) / 100;

            $item['total'] = $itemSubtotal + $itemTax;

            $subtotal += $itemSubtotal;

            $taxAmount += $itemTax;
        }

        $discount = $data['discount_amount'] ?? 0;

        $data['subtotal'] = $subtotal;

        $data['tax_amount'] = $taxAmount;

        $data['grand_total'] =
            $subtotal + $taxAmount - $discount;

        $data['created_by'] = auth()->id();

        return $data;
    }
}
