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
                TextInput::make('order_number')
                    ->default(fn() => 'POS-' . now()->format('YmdHisv'))
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Select::make('pos_outlet_id')
                    ->relationship('outlet', 'name')
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
                                "Room {$record->room_no} - {$record->first_name} {$record->last_name} - RES#{$record->reservation_number}"
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

                Repeater::make('items')
                    ->relationship()
                    ->schema([

                        Select::make('pos_item_id')
                            ->relationship('item', 'name')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {

                                $item = \App\Models\PosItem::find($state);

                                if ($item) {
                                    $set('price', $item->price);
                                }
                            })
                            ->required(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->required(),

                        TextInput::make('price')
                            ->numeric()
                            ->required(),

                        TextInput::make('tax')
                            ->numeric()
                            ->default(0),

                        TextInput::make('total')
                            ->numeric()
                            ->disabled(),

                    ])
                    ->columns(5)
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosOrders::route('/'),
            'create' => CreatePosOrder::route('/create'),
            'edit' => EditPosOrder::route('/{record}/edit'),
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
}
