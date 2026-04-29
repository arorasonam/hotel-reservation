<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Pages\ViewReservation;
use App\Filament\Resources\Reservations\RelationManagers\FoliosRelationManager;
use App\Filament\Resources\Reservations\RelationManagers\PosOrdersRelationManager;
use App\Helpers\HotelContext; // Using Schema instead of Form
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\MealPlan;
use App\Models\Reservation;
use App\Models\ReservationRoomDetail;
use App\Models\RoomType;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use UnitEnum;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Reservations';

    protected static UnitEnum|string|null $navigationGroup = 'Reservation Management';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // filter hotel ID //
        // if (HotelContext::isFiltering()) {
        //     $query->where('hotel_id', HotelContext::selectedId());
        // }

        $user = auth()->user();

        // If SuperAdmin, show everything
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // For HotelAdmin, filter by the hotel group they belong to
        // Assuming your User model has a 'hotel_group_id' or similar relationship

        return $query->whereHas('hotel', function ($q) use ($user) {
            $q->where('hotel_group_id', $user->hotel_group_id);
        });
    }

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
                                        Grid::make(2)->schema([
                                            Select::make('hotel_id')
                                                ->label('Hotel')
                                                ->relationship('hotel', 'name', function ($query) {
                                                    $user = auth()->user();
                                                    // SuperAdmin sees all hotels
                                                    if ($user->hasRole('super_admin')) {
                                                        return $query;
                                                    }

                                                    // HotelAdmin only sees hotels within their assigned group
                                                    return $query->where('hotel_group_id', $user->hotel_group_id);
                                                })
                                                ->default(function () {
                                                    $user = auth()->user();
                                                    $query = Hotel::query();

                                                    // Apply role-based filter to the default search as well
                                                    if (! $user->hasRole('super_admin')) {
                                                        $query->where('hotel_group_id', $user->hotel_group_id);
                                                    }

                                                    return $query->first()?->id;
                                                })
                                                ->required()
                                                ->live()
                                                ->searchable()
                                                ->native(false)
                                                ->columnSpanFull(),
                                        ]),
                                        Grid::make(2)->schema([
                                            Select::make('booking_source_id')
                                                ->label('Booking Source')
                                                ->relationship('bookingSource', 'name', fn ($query, $get) => $query->where('hotel_id', $get('hotel_id')))
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->native(false)
                                                ->inlineLabel(),
                                            Select::make('type')
                                                ->options(['Individual' => 'Individual', 'Walk-in' => 'Walk-in'])
                                                ->native(false)->inlineLabel(),

                                        ]),

                                        // TextInput::make('ref_id')->label('Booking Ref. Id*')->required()->inlineLabel(),

                                        Grid::make(2)->schema([
                                            DatePicker::make('check_in')->label('Arrival Date')->required()->live()->inlineLabel(),
                                            DatePicker::make('check_out')->label('Departure Date')->required()->live()->inlineLabel(),
                                        ]),

                                        Grid::make(2)->schema([
                                            TimePicker::make('check_in_time')->label('Check-in Time')->default('14:00')->seconds(false)->inlineLabel(),
                                            TimePicker::make('check_out_time')->label('Check-out Time')->default('12:00')->seconds(false)->inlineLabel(),
                                        ]),

                                        Grid::make(2)->schema([
                                            Select::make('booking_type_id')
                                                ->label('Booking Type')
                                                ->relationship('bookingType', 'name', fn ($query, $get) => $query->where('hotel_id', $get('hotel_id')))
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->inlineLabel(),
                                            Select::make('source_market_id')
                                                ->label('Source Market')
                                                ->relationship('sourceMarket', 'name', fn ($query, $get) => $query->where('hotel_id', $get('hotel_id')))
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->inlineLabel(),

                                        ]),

                                        // RESTORED: Special PMS Checkboxes

                                        Grid::make(2)->schema([
                                            Select::make('breakfast')->label('Breakfast')->options(['1' => 'Yes', '0' => 'No'])->native(false)->inlineLabel(),
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
                                        Grid::make(3)->schema([
                                            // Checkbox::make('same_plan_all_rooms')->label('Same Plan All Rooms')->default(true)->live(),

                                            Checkbox::make('pay_at_hotel')->label('Pay At Hotel'),
                                            Checkbox::make('is_igst_applied')->label('Is IGST Applied'),


                                        ]),
                                        Grid::make(2)->schema([
                                            Select::make('status')
                                                ->label('Status')
                                                ->options(['confirmed' => 'Confirmed', 'tentative' => 'Tentative'])
                                                ->native(false)
                                                ->inlineLabel()
                                                ->required(),
                                        ]),
                                        Grid::make(1)->schema([
                                            // Checkbox::make('same_plan_all_rooms')->label('Same Plan All Rooms')->default(true)->live(),

                                            Textarea::make('special_requests')->rows(6),
                                        ]),

                                    ]),

                                // 2. FIXED: Category Section (Adds rows properly now)
                                Section::make('Category Selection')
                                    ->compact()
                                    ->schema([
                                        Repeater::make('roomCategories')
                                            ->relationship('roomCategories')
                                            ->schema([
                                                Hidden::make('id'),
                                                // The "Similar to above" Toggle
                                                Toggle::make('similar_to_above')
                                                    ->label('Same as first category')
                                                    ->live()
                                                    ->dehydrated(false)
                                                    ->afterStateUpdated(function ($state, $set, $get) {
                                                        if ($state) {
                                                            // Get all repeater items
                                                            $items = $get('../../roomCategories') ?? [];

                                                            // Grab the first item in the list
                                                            $firstItem = $items[array_key_first($items)] ?? null;

                                                            if ($firstItem) {
                                                                // Sync all fields from the first item to this item
                                                                $set('room_type_id', $firstItem['room_type_id'] ?? null);
                                                                $set('meal_plan_id', $firstItem['meal_plan_id'] ?? null);
                                                                $set('rooms_count', $firstItem['rooms_count'] ?? 1);
                                                                $set('adults', $firstItem['adults'] ?? 2);
                                                                $set('children', $firstItem['children'] ?? 0);
                                                                $set('infants', $firstItem['infants'] ?? 0);
                                                            }
                                                        }
                                                    })
                                                    // Hide the toggle on the very first item since there is nothing "above" it
                                                    ->hidden(fn ($get) => array_key_first($get('../../roomCategories') ?? []) === $get('uuid')),

                                                Grid::make(3)->schema([
                                                    Select::make('room_type_id')
                                                        ->label('Category')
                                                        ->options(fn () => RoomType::pluck('name', 'id'))
                                                        ->live()
                                                        ->required()
                                                        ->afterStateUpdated(function ($state, $set, $get) {
                                                            if (! $state) {
                                                                return;
                                                            }

                                                            // Fetch first room and first meal plan
                                                            $firstRoom = HotelRoom::where('room_type_id', $state)
                                                                ->where('status', 'vacant')
                                                                ->orderBy('room_number')
                                                                ->value('room_number');

                                                            $hotelId = $get('../../hotel_id');
                                                            $firstMealPlan = MealPlan::where('hotel_id', $hotelId)
                                                                ->where('room_type_id', (int) $state)
                                                                ->where('is_active', true)
                                                                ->value('id');

                                                            // Set category-level defaults
                                                            $set('meal_plan_id', $firstMealPlan);

                                                            // Ensure rooms_count is at least 1
                                                            if (! $get('rooms_count')) {
                                                                $set('rooms_count', 1);
                                                            }

                                                            // Initialize the first room row
                                                            $set('roomDetails', [[
                                                                'adults' => 2,
                                                                'children' => 0,
                                                                'infant' => 0,
                                                                'room_number' => $firstRoom ?? 'Auto',
                                                            ]]);
                                                        }),

                                                    Select::make('meal_plan_id')
                                                        ->label('Meal Plan')
                                                        ->placeholder('Select Plan')
                                                        ->options(function ($get) {
                                                            $roomTypeId = $get('room_type_id');
                                                            $hotelId = $get('../../hotel_id');
                                                            if (! $roomTypeId || ! $hotelId) {
                                                                return [];
                                                            }

                                                            return MealPlan::where('hotel_id', $hotelId)
                                                                ->where('room_type_id', (int) $roomTypeId)
                                                                ->where('is_active', true)
                                                                ->pluck('name', 'id');
                                                        })
                                                        ->live()
                                                        ->required()
                                                        ->native(false)
                                                        ->default(function ($get) {
                                                            $roomTypeId = $get('room_type_id');
                                                            $hotelId = $get('../../hotel_id');
                                                            if (! $roomTypeId || ! $hotelId) {
                                                                return null;
                                                            }

                                                            return MealPlan::where('hotel_id', $hotelId)
                                                                ->where('room_type_id', (int) $roomTypeId)
                                                                ->where('is_active', true)
                                                                ->orderBy('name')
                                                                ->value('id');
                                                        }),

                                                    Select::make('rooms_count')
                                                        ->label('Rooms')
                                                        ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5])
                                                        ->default(1)
                                                        ->live()
                                                        ->afterStateHydrated(function ($state, $set, $get) {
                                                            // If it's a new record and no details exist, create the first row
                                                            if (! $get('roomDetails')) {
                                                                $roomTypeId = $get('room_type_id');
                                                                $firstRoom = $roomTypeId
                                                                    ? HotelRoom::where('room_type_id', $roomTypeId)
                                                                        ->where('status', 'vacant')
                                                                        ->orderBy('room_number')
                                                                        ->value('room_number')
                                                                    : 'Auto';

                                                                $set('roomDetails', [[
                                                                    'adults' => 2,
                                                                    'children' => 0,
                                                                    'infant' => 0,
                                                                    'room_number' => $firstRoom ?? 'Auto',
                                                                ]]);
                                                            }
                                                        })
                                                        ->afterStateUpdated(function ($state, $set, $get) {
                                                            $roomTypeId = $get('room_type_id');

                                                            $firstAvailableRoom = $roomTypeId
                                                                ? HotelRoom::where('room_type_id', $roomTypeId)
                                                                    ->where('status', 'vacant')
                                                                    ->orderBy('room_number')
                                                                    ->value('room_number')
                                                                : 'Auto';

                                                            $details = [];
                                                            for ($i = 1; $i <= (int) $state; $i++) {
                                                                $details[] = [
                                                                    'adults' => 2,
                                                                    'children' => 0,
                                                                    'infant' => 0,
                                                                    'room_number' => $firstAvailableRoom ?? 'Auto',
                                                                ];
                                                            }
                                                            $set('roomDetails', $details);
                                                        }),
                                                ]),

                                                Section::make('Requirements')
                                                    ->collapsible()
                                                    ->schema([
                                                        Repeater::make('roomDetails')
                                                            ->relationship('roomDetails')
                                                            ->schema([
                                                                Hidden::make('id'),
                                                                Grid::make(6)->schema([
                                                                    Placeholder::make('room_label')
                                                                        ->label('')
                                                                        ->content(fn ($get, $component) => new HtmlString('<strong>Room '.($get('../../rooms_count') > 1 ? '#' : '').'</strong>'))
                                                                        ->columnSpan(1),

                                                                    Select::make('adults')
                                                                        ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4])
                                                                        ->default(2),

                                                                    Select::make('children')
                                                                        ->options([0 => 0, 1 => 1, 2 => 2])
                                                                        ->default(0),

                                                                    Select::make('infants')
                                                                        ->options([0 => 0, 1 => 1])
                                                                        ->default(0),

                                                                    Select::make('room_number')
                                                                        ->label('Room No.')
                                                                        ->options(function ($get) {
                                                                            // Use the path to the parent Category
                                                                            $roomTypeId = $get('../../room_type_id');
                                                                            if (! $roomTypeId) {
                                                                                return [];
                                                                            }

                                                                            return HotelRoom::where('room_type_id', $roomTypeId)
                                                                                ->where('status', 'vacant')
                                                                                ->pluck('room_number', 'room_number')
                                                                                ->toArray();
                                                                        })
                                                                        ->live()
                                                                        ->required()
                                                                        ->native(false)
                                                                        // This hook triggers when the OPTIONS for this field are updated
                                                                        ->afterStateHydrated(function ($state, $set, $get) {
                                                                            if (! $state) {
                                                                                $roomTypeId = $get('../../room_type_id');
                                                                                if ($roomTypeId) {
                                                                                    $firstRoom = HotelRoom::where('room_type_id', $roomTypeId)
                                                                                        ->where('status', 'vacant')
                                                                                        ->orderBy('room_number')
                                                                                        ->value('room_number');
                                                                                    $set('room_number', $firstRoom);
                                                                                }
                                                                            }
                                                                        })
                                                                        ->columnSpan(2),
                                                                ]),
                                                            ])
                                                            ->addable(false) // Disable manual adding; driven by 'rooms_count'
                                                            ->deletable(false)
                                                            ->reorderable(false),
                                                    ]),
                                            ])
                                            ->addActionLabel('Add More Room Category')
                                            ->cloneable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => RoomType::find($state['room_type_id'] ?? null)?->name ?? 'New Category')
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
                                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                                    ->searchable(['first_name', 'last_name', 'email'])
                                                    ->createOptionForm([
                                                        Grid::make(2)->schema([
                                                            TextInput::make('first_name')->required(),
                                                            TextInput::make('last_name')->required(),
                                                            TextInput::make('email')->email(),
                                                            TextInput::make('phone')->tel(),
                                                            DatePicker::make('birthday')->date(),
                                                            TextInput::make('nationality'),
                                                            Select::make('identity_type')
                                                                ->options([
                                                                    'adhaar_card' => 'Adhaar Card',
                                                                    'payment_receipt' => 'Payment Receipt',
                                                                    'travel_auhtorization' => 'Travel Auhtorization',
                                                                    'cwt_document' => 'CWT Document',
                                                                    'carnet_de_ext' => 'Carnet De Ext.',
                                                                    'fund_certificate' => 'Fund Certificate',
                                                                ]),
                                                            TextInput::make('identity_number'),

                                                            DatePicker::make('identity_expiry'),

                                                            FileUpload::make('identity_document')
                                                                ->label('Upload Identity Proof')
                                                                ->disk('public')   // REQUIRED
                                                                ->directory('guest-identities')
                                                                ->acceptedFileTypes([
                                                                    'image/jpeg',
                                                                    'image/png',
                                                                    'application/pdf',
                                                                ])
                                                                ->maxSize(2048)
                                                                ->downloadable()
                                                                ->openable(),
                                                        ]),
                                                    ])
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if (! $state) {
                                                            return;
                                                        }
                                                        $guest = Guest::find($state);
                                                        if ($guest) {
                                                            $set('first_name', $guest->first_name);
                                                            $set('last_name', $guest->last_name);
                                                            $set('phone', $guest->phone);
                                                            $set('email', $guest->email);
                                                        }
                                                    })->live()->columnSpanFull(),

                                                Grid::make(2)->schema([
                                                    TextInput::make('first_name')->required(),
                                                    TextInput::make('last_name')->required(),
                                                    TextInput::make('email')->required()->email(),
                                                    TextInput::make('phone')->required()->tel(),
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
                                    ->schema([
                                        TextInput::make('base_price')->label('Room Charges')->numeric()->prefix('₹')->live(onBlur: true)
                                            ->afterStateUpdated(fn ($set, $get) => self::calculateTotal($set, $get)),

                                        TextInput::make('tax_amount')->label('Taxes')->numeric()->prefix('₹')->default(0)->live(onBlur: true)
                                            ->afterStateUpdated(fn ($set, $get) => self::calculateTotal($set, $get)),

                                        Placeholder::make('total_display')
                                            ->label('')
                                            ->content(fn ($get) => new HtmlString('
                                                <div class="pt-4 border-t mt-4 text-right">
                                                    <div class="flex justify-between text-lg font-bold">
                                                        <span>Net Payable</span>
                                                        <span class="text-primary-600">₹'.number_format((float) ($get('total_amount') ?? 0), 2).'</span>
                                                    </div>
                                                </div>
                                            ')),
                                        Placeholder::make('bill_details')
                                            ->label('')
                                            ->content(fn($record) => view('filament.components.bill-summary-display', [
                                                'record' => $record,
                                            ]))
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpan(['lg' => 1]) // Sidebars like your screenshot

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
        $base = (float) $get('base_price') ?? 0;
        $tax = (float) $get('tax_amount') ?? 0;
        $disc = (float) $get('discount_amount') ?? 0;
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
                Tables\Columns\TextColumn::make('hotel.name')
                    ->label('Hotel')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->url(fn ($record): string => route('filament.admin.resources.hotels.view', ['record' => $record->hotel_id]))
                    ->openUrlInNewTab(),
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
                // Tables\Columns\TextColumn::make('roomType.name')->label('Room Type'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'tentative' => 'info',
                        'waitlist' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Folio Balance')
                    ->money('INR'),
                // Tables\Columns\TextColumn::make('total_pos_charges')
                //     ->label('Total Charges')
                //     ->money('INR')
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
                // ActionGroup::make([
                // 1. Group Check-in Action
                Action::make('partialCheckIn')
                    ->label('Partial Check-in')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    // Only show if there are rooms that AREN'T checked in yet
                    ->visible(
                        fn ($record) => $record->roomCategories()
                            ->whereHas('roomDetails', fn ($q) => $q->where('status', '!=', 'checked_in'))
                            ->exists()
                    )
                    ->form([
                        CheckboxList::make('selected_room_details')
                            ->label('Select Rooms to Check-in')
                            ->options(function ($record) {
                                // 1. Fetch only the Category IDs belonging to this Reservation
                                $categoryIds = $record->roomCategories()->pluck('id');

                                // 2. Query the Details table directly using those Category IDs
                                // 3. Filter out 'checked_out' rooms as they shouldn't be checked in again
                                return ReservationRoomDetail::whereIn('category_id', $categoryIds)
                                    ->where('status', '!=', 'checked_out')
                                    ->where('status', '!=', 'checked_in')
                                    ->get()
                                    ->mapWithKeys(fn ($detail) => [
                                        // Use the unique detail ID as the key to prevent selection conflicts
                                        $detail->id => 'Room '.($detail->room_number ?? 'Auto').' ('.ucfirst($detail->status ?? 'Confirmed').')',
                                    ]);
                            })
                            ->required()
                            ->columns(2),
                    ])
                    ->action(function ($record, array $data) {
                        $selectedIds = $data['selected_room_details'];

                        foreach ($selectedIds as $detailId) {
                            $detail = ReservationRoomDetail::find($detailId);

                            if ($detail) {
                                // 1. Update the individual room detail status
                                $detail->update(['status' => 'checked_in']);

                                // 2. Sync with the physical HotelRoom table
                                if ($detail->room_number && $detail->room_number !== 'Auto') {
                                    HotelRoom::where('room_number', $detail->room_number)
                                        ->update(['status' => 'occupied']);
                                }
                            }
                        }

                        // 3. Logic to update parent reservation status if all rooms are now checked in
                        self::syncParentStatus($record);

                        Notification::make()
                            ->title(count($selectedIds).' rooms successfully checked in')
                            ->success()
                            ->send();
                    }),
                Action::make('groupCheckIn')
                    ->label('Group Check-in')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn ($record) => $record->status === 'checked_in')
                    ->requiresConfirmation()
                    ->action(fn ($record) => self::processGroupStatusChange($record, 'checked_in')),

                // 2. Group Check-out Action
                Action::make('groupCheckOut')
                    ->label('Group Check-out')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'checked_in')
                    ->requiresConfirmation()
                    ->action(fn ($record) => self::processGroupStatusChange($record, 'checked_out')),
                ViewAction::make()->icon('heroicon-m-eye'),
                EditAction::make()->icon('heroicon-m-pencil-square'),
                DeleteAction::make()->icon('heroicon-m-trash'),
                // ])->label('Actions')->icon('heroicon-m-ellipsis-vertical'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit' => EditReservation::route('/{record}/edit'),
            'view' => ViewReservation::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PosOrdersRelationManager::class,
            FoliosRelationManager::class,
        ];
    }

    protected static function processGroupStatusChange($reservation, string $status): void
    {
        // Update parent status
        $reservation->update(['status' => $status]);

        $physicalStatus = match ($status) {
            'checked_in' => 'occupied',
            'checked_out' => 'dirty',
            default => 'vacant',
        };

        // Update all rooms in the tiered structure
        foreach ($reservation->roomCategories as $category) {
            foreach ($category->roomDetails as $detail) {
                $detail->update(['status' => $status]);

                if ($detail->room_number && $detail->room_number !== 'Auto') {
                    HotelRoom::where('room_number', $detail->room_number)
                        ->update(['status' => $physicalStatus]);
                }
            }
        }

        Notification::make()
            ->title('Reservation '.str_replace('_', ' ', $status).' successfully')
            ->success()
            ->send();
    }

    protected static function syncParentStatus($reservation): void
    {
        $details = ReservationRoomDetail::whereIn(
            'category_id',
            $reservation->roomCategories()->pluck('id')
        )->get();

        $total = $details->count();
        $checkedIn = $details->where('status', 'checked_in')->count();
        $checkedOut = $details->where('status', 'checked_out')->count();

        if ($checkedOut === $total) {
            $reservation->update(['status' => 'checked_out']);
        } elseif ($checkedIn + $checkedOut === $total) {
            $reservation->update(['status' => 'checked_in']);
        }
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([

    //             Tabs::make('Reservation Details')
    //                 ->tabs([

    //                     Tab::make('Guest Information')
    //                         ->icon('heroicon-m-user')
    //                         ->schema([

    //                             RepeatableEntry::make('reservationGuests')
    //                                 ->label('Guests')
    //                                 ->schema([

    //                                     TextEntry::make('first_name')
    //                                         ->label('First Name'),

    //                                     TextEntry::make('last_name')
    //                                         ->label('Last Name'),

    //                                     TextEntry::make('email'),

    //                                     TextEntry::make('phone'),

    //                                     TextEntry::make('nationality'),

    //                                     TextEntry::make('birthday')
    //                                         ->date(),

    //                                     TextEntry::make('is_primary')
    //                                         ->badge()
    //                                         ->formatStateUsing(fn($state) => $state ? 'Primary Guest' : 'Guest')
    //                                         ->color(fn($state) => $state ? 'success' : 'gray'),
    //                                 ])
    //                                 ->columns(3),

    //                         ]),

    //                     Tab::make('Stay Details')
    //                         ->icon('heroicon-m-calendar')
    //                         ->schema([

    //                             Section::make()
    //                                 ->schema([

    //                                     TextEntry::make('hotel.name')
    //                                         ->label('Hotel'),

    //                                     TextEntry::make('check_in')
    //                                         ->date(),

    //                                     TextEntry::make('check_out')
    //                                         ->date(),

    //                                     TextEntry::make('roomType.name')
    //                                         ->label('Room Type'),

    //                                     TextEntry::make('room_no')
    //                                         ->label('Room Number'),

    //                                 ])
    //                                 ->columns(2),

    //                         ]),

    //                     Tab::make('Payment Information')
    //                         ->icon('heroicon-m-credit-card')
    //                         ->schema([

    //                             TextEntry::make('payment_method')
    //                                 ->badge()
    //                                 ->color(fn($state) => match ($state) {
    //                                     'credit_card' => 'success',
    //                                     'cash' => 'info',
    //                                     'bank_transfer' => 'warning',
    //                                     default => 'gray',
    //                                 }),

    //                             TextEntry::make('card_number')
    //                                 ->label('Card Number'),

    //                         ]),

    //                     Tab::make('Special Requests')
    //                         ->icon('heroicon-m-chat-bubble-bottom-center-text')
    //                         ->schema([

    //                             TextEntry::make('special_requests')
    //                                 ->placeholder('No special requests provided'),

    //                         ]),

    //                 ]),

    //         ]);
    // }
}
