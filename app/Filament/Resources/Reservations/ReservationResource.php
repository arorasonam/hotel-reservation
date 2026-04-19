<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Models\Reservation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // Using Schema instead of Form
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use BackedEnum;
use UnitEnum;
use App\Models\HotelRoom;
use Filament\Forms\Components\TimePicker;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Toggle;
use App\Models\RoomType;
use App\Models\Guest;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use App\Models\bookingSource;
use App\Models\SourceMarket;
use App\Models\BookingType;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Reservations';

    protected static UnitEnum|string|null $navigationGroup = 'Reservation Management';

    protected static ?int $navigationSort = 1;

    /**
     * Requirement: Tabbed Interface using Schema
     */
    public static function form(Schema $schema): Schema
    {

        return $schema
            ->components([
                // Constrains width to 1280px (max-w-7xl) to prevent the "Massive" UI look
                Grid::make(12)
                    ->extraAttributes(['class' => 'max-w-7xl mx-auto p-4'])
                    ->columnSpanFull()
                    ->schema([

                        // --- LEFT COLUMN: 8/12 Span ---
                        Grid::make(1)
                            ->columnSpan(8)
                            ->schema([

                                // 1. RESTORED: Full Booking Details
                                Section::make('Booking Details')
                                    ->compact()
                                    ->schema([
                                        Select::make('hotel_id')
                                            ->relationship('hotel', 'name')
                                            ->live()->required()->inlineLabel(),
                                        Grid::make(2)->schema([
                                            Select::make('booking_source_id')
                                                ->label('Booking Source')
                                                ->relationship('bookingSource', 'name', fn($query, $get) => $query->where('hotel_id', $get('hotel_id')))
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->native(false)
                                                ->inlineLabel(),
                                            Select::make('type')
                                                ->options(['Individual' => 'Individual', 'Walk-in' => 'Walk-in'])
                                                ->native(false)->inlineLabel(),
                                        ]),

                                        TextInput::make('ref_id')->label('Booking Ref. Id*')->required()->inlineLabel(),

                                        Grid::make(2)->schema([
                                            DatePicker::make('check_in')->label('Arrival Date')->required()->live()->inlineLabel(),
                                            DatePicker::make('check_out')->label('Departure Date')->required()->live()->inlineLabel(),
                                        ]),
                                        Select::make('booking_type_id')
                                            ->label('Booking Type')
                                            ->relationship('bookingType', 'name', fn($query, $get) => $query->where('hotel_id', $get('hotel_id')))
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->inlineLabel(),

                                        Grid::make(2)->schema([
                                            TimePicker::make('check_in_time')->label('Check-in Time')->default('14:00')->seconds(false)->inlineLabel(),
                                            TimePicker::make('check_out_time')->label('Check-out Time')->default('12:00')->seconds(false)->inlineLabel(),
                                        ]),

                                        Grid::make(2)->schema([
                                            Select::make('source_market_id')
                                                ->label('Source Market')
                                                ->relationship('sourceMarket', 'name', fn($query, $get) => $query->where('hotel_id', $get('hotel_id')))
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->inlineLabel(),
                                            Select::make('breakfast_plan')->label('Breakfast')->options(['yes' => 'Yes', 'no' => 'No'])->inlineLabel(),
                                        ]),

                                        // RESTORED: Special PMS Checkboxes
                                        Grid::make(3)->schema([
                                            // Checkbox::make('same_plan_all_rooms')->label('Same Plan All Rooms')->default(true)->live(),
                                            Checkbox::make('pay_at_hotel')->label('Pay At Hotel'),
                                            Checkbox::make('is_igst_applied')->label('Is IGST Applied'),
                                        ]),

                                        Select::make('rate_plan')
                                            ->label('Rate Plan')
                                            ->options([
                                                'Best Available Rates' => 'Best Available Rates',
                                                'Corporate Rate' => 'Corporate Rate',
                                                'Seasonal Offer' => 'Seasonal Offer',
                                            ])
                                            ->default('Best Available Rates') // This only sets the UI, options() is required for storage
                                            ->required()
                                            ->native(false)
                                            ->inlineLabel(),
                                    ]),

                                // 2. FIXED: Category Section (Adds rows properly now)
                                Section::make('Category Selection')
                                    ->compact()
                                    ->schema([


                                        Repeater::make('room_requirements')
                                            ->relationship('room_requirements')
                                            ->schema([
                                                // The "Similar to above" Toggle
                                                Toggle::make('similar_to_above')
                                                    ->label('Same as first category')
                                                    ->live()
                                                    ->dehydrated(false)
                                                    ->afterStateUpdated(function ($state, $set, $get) {
                                                        if ($state) {
                                                            // Get all repeater items
                                                            $items = $get('../../room_requirements');

                                                            // Grab the first item in the list
                                                            $firstItem = $items[array_key_first($items)] ?? null;

                                                            if ($firstItem) {
                                                                // Sync all fields from the first item to this item
                                                                $set('room_type_id', $firstItem['room_type_id'] ?? null);
                                                                $set('meal_plan', $firstItem['meal_plan'] ?? 'EP');
                                                                $set('rooms_count', $firstItem['rooms_count'] ?? 1);
                                                                $set('adults', $firstItem['adults'] ?? 2);
                                                                $set('children', $firstItem['children'] ?? 0);
                                                                $set('infants', $firstItem['infants'] ?? 0);
                                                            }
                                                        }
                                                    })
                                                    // Hide the toggle on the very first item since there is nothing "above" it
                                                    ->hidden(fn($get) => array_key_first($get('../../room_requirements')) === $get('uuid')),

                                                Grid::make(3)->schema([
                                                    Select::make('room_type_id')
                                                        ->label('Category')
                                                        ->options(fn($get) => \App\Models\RoomType::pluck('name', 'id'))
                                                        ->live()->required()
                                                        ->disabled(fn($get) => $get('similar_to_above')), // Lock field if synced

                                                    Select::make('meal_plan_id')
                                                        ->label('Meal Plan')
                                                        ->placeholder('Select Plan')
                                                        ->options(function ($get) {
                                                            // 1. Get the Room Type from the current repeater row
                                                            $roomTypeId = $get('room_type_id');

                                                            // 2. Get the Hotel ID from the parent form
                                                            $hotelId = $get('../../hotel_id');

                                                            // Return empty if either is missing to prevent errors
                                                            if (!$roomTypeId || !$hotelId) {
                                                                return [];
                                                            }
                                                            // 3. Fetch meal plans specific to this property AND category
                                                            return \App\Models\MealPlan::where('hotel_id', $hotelId)
                                                                ->where('room_type_id', (int) $roomTypeId)
                                                                ->where('is_active', true)
                                                                ->pluck('name', 'id');
                                                        })
                                                        ->live()
                                                        ->required()
                                                        ->native(false)
                                                        ->disabled(fn($get) => !$get('room_type_id') || $get('similar_to_above')),

                                                    Select::make('rooms_count')
                                                        ->label('Rooms')
                                                        ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5])
                                                        ->live()
                                                        ->default(1)
                                                        ->columnSpan(1)
                                                        ->afterStateUpdated(function ($state, $set, $get) {
                                                            // Logic to pre-populate the 'Requirements' section based on count
                                                            $requirements = [];
                                                            for ($i = 1; $i <= (int)$state; $i++) {
                                                                $requirements[] = [
                                                                    'adults' => 2,
                                                                    'children' => 0,
                                                                    'infant' => 0,
                                                                    'room_number' => 'Auto',
                                                                ];
                                                            }
                                                            $set('requirements', $requirements);
                                                        }),
                                                ]),

                                                Section::make('Requirements')
                                                    ->collapsible()
                                                    ->schema([
                                                        Repeater::make('requirements')
                                                            ->schema([
                                                                Grid::make(6)->schema([
                                                                    Placeholder::make('room_label')
                                                                        ->label('')
                                                                        ->content(fn($get, $component) => new HtmlString('<strong>Room ' . ($get('../../rooms_count') > 1 ? '#' : '') . '</strong>'))
                                                                        ->columnSpan(1),

                                                                    Select::make('adults')
                                                                        ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4])
                                                                        ->default(2),

                                                                    Select::make('children')
                                                                        ->options([0 => 0, 1 => 1, 2 => 2])
                                                                        ->default(0),

                                                                    Select::make('infant')
                                                                        ->options([0 => 0, 1 => 1])
                                                                        ->default(0),

                                                                    Select::make('room_number')
                                                                        ->label('Room No.')
                                                                        ->options(function ($get) {
                                                                            $roomTypeId = $get('../../room_type_id');
                                                                            if (!$roomTypeId) return ['Auto' => 'Auto'];

                                                                            return ['Auto' => 'Auto'] + \App\Models\HotelRoom::where('room_type_id', $roomTypeId)
                                                                                ->where('status', 'vacant')
                                                                                ->pluck('room_number', 'room_number')
                                                                                ->toArray();
                                                                        })
                                                                        ->default('Auto')
                                                                        ->columnSpan(2),
                                                                ]),
                                                            ])
                                                            ->addable(false) // Disable manual adding; driven by 'rooms_count'
                                                            ->deletable(false)
                                                            ->reorderable(false)
                                                    ])
                                            ])
                                            ->addActionLabel('Add More Room Category')
                                            ->cloneable()
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => RoomType::find($state['room_type_id'] ?? null)?->name ?? 'New Category')
                                            ->extraAttributes(['class' => 'bg-gray-50/50 p-2 rounded-lg border border-gray-100']),
                                    ]),

                                // 3. RESTORED: Full Guest Search & Popup functionality
                                Section::make('Guest Information')
                                    ->compact()
                                    ->schema([
                                        Repeater::make('reservationGuests')
                                            ->relationship('reservationGuests')
                                            ->schema([
                                                Select::make('guest_id')
                                                    ->label('Find Existing Guest')
                                                    ->relationship('guest', 'first_name')
                                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                                                    ->searchable(['first_name', 'last_name', 'email'])
                                                    ->createOptionForm([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('first_name')->required(),
                                                            TextInput::make('last_name')->required(),
                                                            TextInput::make('email')->email(),
                                                            TextInput::make('phone')->tel(),
                                                        ]),
                                                    ])
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if (!$state) return;
                                                        $guest = Guest::find($state);
                                                        if ($guest) {
                                                            $set('first_name', $guest->first_name);
                                                            $set('last_name', $guest->last_name);
                                                        }
                                                    })->live()->columnSpanFull(),

                                                Grid::make(2)->schema([
                                                    TextInput::make('first_name')->required(),
                                                    TextInput::make('last_name')->required(),
                                                ]),
                                                Toggle::make('is_primary')->label('Primary Guest')->onIcon('heroicon-m-star'),
                                            ])->addActionLabel('Add Guest')->collapsible(),
                                    ]),
                            ]),

                        // --- RIGHT COLUMN: Sidebar (4/12) ---
                        Grid::make(1)
                            ->columnSpan(4)
                            ->schema([
                                Section::make('Bill Summary')
                                    ->compact()
                                    ->schema([
                                        TextInput::make('base_price')->label('Room Charges')->numeric()->prefix('₹')->live(onBlur: true)
                                            ->afterStateUpdated(fn($set, $get) => self::calculateTotal($set, $get)),

                                        TextInput::make('tax_amount')->label('Taxes')->numeric()->prefix('₹')->default(0)->live(onBlur: true)
                                            ->afterStateUpdated(fn($set, $get) => self::calculateTotal($set, $get)),

                                        Placeholder::make('total_display')
                                            ->label('')
                                            ->content(fn($get) => new HtmlString('
                                                <div class="pt-4 border-t mt-4 text-right">
                                                    <div class="flex justify-between text-lg font-bold">
                                                        <span>Net Payable</span>
                                                        <span class="text-primary-600">₹' . number_format((float)($get('total_amount') ?? 0), 2) . '</span>
                                                    </div>
                                                </div>
                                            ')),

                                        Select::make('status')
                                            ->options(['confirmed' => 'Confirmed', 'tentative' => 'Tentative'])
                                            ->required(),
                                    ]),

                                Section::make('Internal Notes')->compact()->schema([
                                    Textarea::make('special_requests')->rows(6),
                                ]),
                            ]),
                    ]),
            ]);
    }

    protected static function updateNights($cin, $cout, $set)
    {
        if ($cin && $cout) {
            $nights = Carbon::parse($cin)->diffInDays(Carbon::parse($cout));
            $set('nights', $nights > 0 ? $nights : 1);
        }
    }

    protected static function calculateTotal($set, $get)
    {
        $base = (float)$get('base_price') ?? 0;
        $tax = (float)$get('tax_amount') ?? 0;
        $disc = (float)$get('discount_amount') ?? 0;
        $set('total_amount', ($base + $tax) - $disc);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('reservation_number')
                    ->label('Reservation Number')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Optional: allows staff to click to copy
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('primary_guest_name')
                    ->label('Guest Name')
                    ->state(function ($record) {
                        // Fetch the guest marked as primary from the relationship
                        $primaryGuest = $record->reservationGuests()
                            ->where('is_primary', true)
                            ->first();

                        // Fallback to the first guest if no primary is marked, or a placeholder
                        if ($primaryGuest) {
                            return trim("{$primaryGuest->first_name} {$primaryGuest->last_name}");
                        }

                        $firstGuest = $record->reservationGuests()->first();
                        return $firstGuest
                            ? trim("{$firstGuest->first_name} {$firstGuest->last_name}")
                            : 'No Guest Assigned';
                    })
                    // Ensure the column remains searchable via the related table
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('reservationGuests', function ($q) use ($search) {
                            $q->where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('check_in')->label('Arrival Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('check_out')->label('Departure Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('roomType.name')->label('Room Type'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'tentative' => 'info',
                        'waitlist' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_pos_charges')
                    ->label('Total Charges')
                    ->money('INR')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'confirmed' => 'Confirmed',
                        'tentative' => 'Tentative',
                        'waitlist' => 'Waitlist',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                EditAction::make()->icon('heroicon-m-pencil-square'),
                DeleteAction::make()->icon('heroicon-m-trash'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit' => EditReservation::route('/{record}/edit'),
        ];
    }
}
