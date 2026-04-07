<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Models\Reservation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // Using Schema instead of Form
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Set;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Get;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Reservations';

    protected static UnitEnum|string|null $navigationGroup = 'Management';

    /**
     * Requirement: Tabbed Interface using Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Reservation Details')
                    ->tabs([
                        Tab::make('Guest Information')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Repeater::make('reservationGuests')
                                    ->relationship('reservationGuests')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('guest_id')
                                                    ->label('Search Existing Guest')
                                                    ->relationship('guest', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if (!$state) return;
                                                        $user = \App\Models\User::find($state);
                                                        if ($user) {
                                                            $set('first_name', $user->first_name);
                                                            $set('last_name', $user->last_name);
                                                            $set('email', $user->email);
                                                            $set('phone', $user->phone);
                                                        }
                                                    })
                                                    /** RESTORED: Add New Guest Option **/
                                                    ->createOptionForm([
                                                        Grid::make(2)
                                                            ->schema([
                                                                TextInput::make('first_name')->required(),
                                                                TextInput::make('last_name')->required(),
                                                                TextInput::make('email')->email()->unique('users', 'email'),
                                                                TextInput::make('phone')->tel(),
                                                            ]),
                                                    ])
                                                    ->createOptionUsing(function (array $data) {
                                                        // Satisfy the NOT NULL 'name' constraint on users table
                                                        $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);
                                                        $data['role'] = 'user';
                                                        $data['password'] = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(10));

                                                        return \App\Models\User::create($data)->id;
                                                    })
                                                    ->columnSpan(2),

                                                Toggle::make('is_primary')
                                                    ->label('Primary?')
                                                    ->onIcon('heroicon-m-star')
                                                    ->offIcon('heroicon-o-star')
                                                    ->default(false),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('first_name')->required(),
                                                TextInput::make('last_name')->required(),
                                                TextInput::make('email')->email(),
                                                TextInput::make('phone')->tel(),
                                            ]),
                                    ])
                                    ->itemLabel(fn(array $state): ?string => ($state['first_name'] ?? '') . ' ' . ($state['last_name'] ?? ''))
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->addActionLabel('Add more Guest[s]')
                                    ->columnSpanFull(),
                            ]),

                        // Inside the form() method, update the Stay Details Tab:
                        Tab::make('Stay Details')
                            ->icon('heroicon-m-calendar')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Select::make('hotel_id')
                                            ->label('Select Hotel')
                                            ->relationship('hotel', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->columnSpanFull(),

                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('check_in')
                                                    ->label('Arrival Date')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn($state, $set, $get) => self::updateNights($state, $get('check_out'), $set)),

                                                DatePicker::make('check_out')
                                                    ->label('Departure Date')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn($state, $set, $get) => self::updateNights($get('check_in'), $state, $set)),

                                                Select::make('room_type_id')
                                                    ->label('Room Type')
                                                    ->placeholder('Select a hotel first')
                                                    ->options(function (callable $get) {
                                                        $hotelId = $get('hotel_id');
                                                        if (!$hotelId) return [];

                                                        return \App\Models\RoomType::pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->live(), // Important: Must be live for Room No to react

                                                Select::make('room_no')
                                                    ->label('Room No')
                                                    ->placeholder('Select a room type first')
                                                    ->options(function (callable $get) {
                                                        $roomTypeId = $get('room_type_id');
                                                        if (!$roomTypeId) return [];

                                                        // This must return an array where the KEY matches what is in your DB
                                                        return \App\Models\HotelRoom::where('room_type_id', $roomTypeId)
                                                            ->pluck('room_number', 'room_number')
                                                            ->toArray();
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->live(),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Payment Information')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Select::make('payment_method')
                                    ->options([
                                        'credit_card' => 'Credit Card',
                                        'cash' => 'Cash',
                                        'bank_transfer' => 'Bank Transfer',
                                    ]),
                                TextInput::make('card_number')
                                    ->label('Credit Card Details')
                                    ->mask('9999-9999-9999-9999'),
                            ]),

                        Tab::make('Special Requests')
                            ->icon('heroicon-m-chat-bubble-bottom-center-text')
                            ->schema([
                                Textarea::make('special_requests')
                                    ->label('Special Requests / Notes')
                                    ->placeholder('Enter any additional guest requests or notes here...')
                                    ->rows(5),
                            ]),
                    ])->columnSpanFull(),
            ]);
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

    protected static function updateNights($cin, $cout, $set)
    {
        if ($cin && $cout) {
            $set('nights', \Carbon\Carbon::parse($cin)->diffInDays(\Carbon\Carbon::parse($cout)));
        }
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
