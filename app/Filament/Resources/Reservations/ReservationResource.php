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
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Set;
use BackedEnum;
use UnitEnum;

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
                                Select::make('guest_id')
                                    ->label('Existing Guest Lookup')
                                    ->relationship('guest', 'first_name') // or 'full_name' if you have a virtual attribute
                                    ->getOptionLabelFromRecordUsing(fn($record) => trim("{$record->first_name} {$record->last_name}") ?: $record->email ?: 'Unknown Guest')
                                    ->searchable()
                                    ->preload()
                                    // 1. This adds the "+" button and "Add..." option if no match is found
                                    ->createOptionForm([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('first_name')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('last_name')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->unique('users', 'email'),
                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->placeholder('+1 201-555-0123'),
                                                Select::make('gender')
                                                    ->options([
                                                        'male' => 'Male',
                                                        'female' => 'Female',
                                                        'other' => 'Other',
                                                    ]),
                                                DatePicker::make('dob')
                                                    ->label('Date of Birth'),
                                                Select::make('nationality')
                                                    ->options([
                                                        'us' => 'American',
                                                        'in' => 'Indian',
                                                        // Add more as needed
                                                    ])
                                                    ->searchable(),
                                                Select::make('purpose_of_visit')
                                                    ->options([
                                                        'leisure' => 'Leisure',
                                                        'business' => 'Business',
                                                    ]),
                                                Textarea::make('guest_preferences')
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    // 2. This logic runs when the "Save" button in the popup is clicked
                                    ->createOptionUsing(function (array $data) {
                                        // Ensure the new user is created with the correct Role
                                        $data['role'] = 'user';
                                        $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);
                                        // If your User model uses a password, you might want to generate a random one
                                        $data['password'] = bcrypt(\Illuminate\Support\Str::random(10));

                                        return \App\Models\User::create($data)->id;
                                    })
                                    ->live() // Essential: makes the field reactive
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (! $state) return;

                                        $user = \App\Models\User::find($state);

                                        if ($user) {
                                            $set('first_name', $user->first_name);
                                            $set('last_name', $user->last_name);
                                            $set('email', $user->email);
                                            $set('phone', $user->phone);
                                        }
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('first_name')->required(),
                                TextInput::make('last_name')->required(),
                                TextInput::make('email')->email(),
                                TextInput::make('phone')->tel(),
                            ])->columns(2),

                        Tab::make('Stay Details')
                            ->icon('heroicon-m-calendar')
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
                                    ->relationship('roomType', 'name')
                                    ->required(),
                                TextInput::make('adults')
                                    ->label('Number of Guests')
                                    ->numeric()
                                    ->default(1),
                                Select::make('rate_plan')
                                    ->label('Rate Management')
                                    ->options([
                                        'standard' => 'Standard Rate',
                                        'promo' => 'Promotional Code',
                                        'corp' => 'Corporate Rate',
                                    ]),
                            ])->columns(2),

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
                Tables\Columns\TextColumn::make('id')->label('Reservation Number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Guest Name')
                    ->state(fn($record) => trim("{$record->first_name} {$record->last_name}"))
                    ->searchable(['first_name', 'last_name']),
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
