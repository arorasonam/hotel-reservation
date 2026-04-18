<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Pages\ViewReservation;
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
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Set;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\Reservations\RelationManagers\PosOrdersRelationManager;
use App\Filament\Resources\Reservations\RelationManagers\FoliosRelationManager;

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
                                                    // Update relationship to point to 'guest' (from Guests table)
                                                    ->relationship('guest', 'first_name') // Changed 'name' to 'first_name'
                                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}") // Shows both in dropdown
                                                    ->searchable(['first_name', 'last_name']) // Allows searching by both
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if (!$state) return;

                                                        // Explicitly query the Guest model instead of User
                                                        $guest = \App\Models\Guest::find($state);
                                                        if ($guest) {
                                                            $set('first_name', $guest->first_name);
                                                            $set('last_name', $guest->last_name);
                                                            $set('email', $guest->email);
                                                            $set('phone', $guest->phone);
                                                            $set('birthday', $guest->birthday);
                                                            $set('nationality', $guest->nationality);
                                                        }
                                                    })
                                                    ->createOptionForm([
                                                        Grid::make(2)
                                                            ->schema([
                                                                TextInput::make('first_name')->required(),
                                                                TextInput::make('last_name')->required(),
                                                                TextInput::make('email')->email()->unique('guests', 'email'),
                                                                TextInput::make('phone')->tel(),
                                                                DatePicker::make('birthday')->date(),
                                                                TextInput::make('nationality')->placeholder('e.g., American, Canadian'),
                                                            ]),
                                                    ])
                                                    ->createOptionUsing(function (array $data) {
                                                        // Create record in 'guests' table
                                                        $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);
                                                        return \App\Models\Guest::create($data)->id;
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

                        Tab::make('Stay Details')
                            ->icon('heroicon-m-calendar')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Select::make('hotel_id')
                                            ->label('Select Hotel')
                                            ->relationship(
                                                name: 'hotel',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function (Builder $query) {
                                                    $user = auth()->user();

                                                    // Apply filter if user is a hotel_admin with a specific group
                                                    if ($user->hasRole('hotel_admin') && $user->hotel_group_id) {
                                                        return $query->where('hotel_group_id', $user->hotel_group_id);
                                                    }

                                                    return $query;
                                                }
                                            )
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
                                                        return \App\Models\RoomType::pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->live(),

                                                Select::make('room_no')
                                                    ->label('Room No')
                                                    ->placeholder('Select room type first')
                                                    ->options(function (callable $get) {
                                                        $roomTypeId = $get('room_type_id');
                                                        if (!$roomTypeId) return [];

                                                        return \App\Models\HotelRoom::where('room_type_id', $roomTypeId)
                                                            ->pluck('room_number', 'room_number')
                                                            ->toArray();
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->live(),
                                            ]),
                                    ]),
                            ]), // Added closing brackets for Section/Tab

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
                Tables\Columns\TextColumn::make('total_pos_charges')
                        ->label('POS Charges')
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
                ViewAction::make(),
                EditAction::make()->icon('heroicon-m-pencil-square'),
                DeleteAction::make()->icon('heroicon-m-trash'),
                // Action::make('checkoutGuest')
                // ->label('Checkout Guest')
                // ->icon('heroicon-o-credit-card')
                // ->form([
                //     Select::make('payment_method')
                //         ->options([
                //             'cash' => 'Cash',
                //             'card' => 'Card',
                //             'upi' => 'UPI',
                //         ])
                //         ->required(),

                //     TextInput::make('amount')
                //         ->numeric()
                //         ->required(),

                // ])
                // ->action(function ($record, $data) {

                //     if ($record->remaining_balance > 0) {

                //         \Filament\Notifications\Notification::make()
                //             ->title('Payment pending before checkout')
                //             ->danger()
                //             ->send();

                //         return;
                //     }

                //     // Create checkout Payment
                //     POSPayment::create([

                //         'reservation_id' => $record->id,

                //         'payment_method' => $data['payment_method'],

                //         'amount' => $data['amount'],

                //         'paid_at' => now(),

                //     ]);

                //     $totalCharges =
                //         $record->folios()->sum('amount');

                //     $totalPaid =
                //         $record->payments()->sum('amount');

                //     if ($totalPaid >= $totalCharges) {

                //         $record->update([
                //             'status' => 'checked_out'
                //         ]);
                //     }
                // })
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
            'view' => ViewReservation::route('/{record}'),
        ];
    }

     /*
    |--------------------------------------------------------------------------
    | RELATION MANAGERS
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [
            PosOrdersRelationManager::class,
            FoliosRelationManager::class
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('Reservation Details')
                    ->tabs([

                        Tab::make('Guest Information')
                            ->icon('heroicon-m-user')
                            ->schema([

                                RepeatableEntry::make('reservationGuests')
                                    ->label('Guests')
                                    ->schema([

                                        TextEntry::make('first_name')
                                            ->label('First Name'),

                                        TextEntry::make('last_name')
                                            ->label('Last Name'),

                                        TextEntry::make('email'),

                                        TextEntry::make('phone'),

                                        TextEntry::make('nationality'),

                                        TextEntry::make('birthday')
                                            ->date(),

                                        TextEntry::make('is_primary')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state ? 'Primary Guest' : 'Guest')
                                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                                    ])
                                    ->columns(3),

                            ]),


                        Tab::make('Stay Details')
                            ->icon('heroicon-m-calendar')
                            ->schema([

                                Section::make()
                                    ->schema([

                                        TextEntry::make('hotel.name')
                                            ->label('Hotel'),

                                        TextEntry::make('check_in')
                                            ->date(),

                                        TextEntry::make('check_out')
                                            ->date(),

                                        TextEntry::make('roomType.name')
                                            ->label('Room Type'),

                                        TextEntry::make('room_no')
                                            ->label('Room Number'),

                                    ])
                                    ->columns(2),

                            ]),


                        Tab::make('Payment Information')
                            ->icon('heroicon-m-credit-card')
                            ->schema([

                                TextEntry::make('payment_method')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'credit_card' => 'success',
                                        'cash' => 'info',
                                        'bank_transfer' => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('card_number')
                                    ->label('Card Number'),

                            ]),


                        Tab::make('Special Requests')
                            ->icon('heroicon-m-chat-bubble-bottom-center-text')
                            ->schema([

                                TextEntry::make('special_requests')
                                    ->placeholder('No special requests provided'),

                            ]),

                    ])

            ]);
    }

}
