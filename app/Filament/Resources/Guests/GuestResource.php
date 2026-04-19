<?php

namespace App\Filament\Resources\Guests;

use App\Models\Guest;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Guests\Pages;
use App\Filament\Resources\Guests\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Guests\RelationManagers\ReservationsRelationManager;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Forms\Components\Select;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Guests';
    protected static UnitEnum|string|null $navigationGroup = 'Guest Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Guest';
    protected static ?string $pluralModelLabel = 'Guests';


    /*
    |--------------------------------------------------------------------------
    | FORM (Create / Edit)
    |--------------------------------------------------------------------------
    */

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                Tabs::make('Guest Tabs')
                    ->columnSpanFull()
                    ->tabs([

                        Tab::make('Personal Information')
                            ->schema([

                                FileUpload::make('profile_photo')
                                    ->label('Profile Picture')
                                    ->image()
                                    ->disk('public')   // REQUIRED
                                    ->directory('guest-profile-photos')
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->avatar()
                                    ->nullable(),

                                TextInput::make('first_name')
                                    ->required(),

                                TextInput::make('last_name')
                                    ->required(),

                                TextInput::make('email')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->email(),

                                TextInput::make('phone')
                                    ->required()
                                    ->tel()
                                    ->maxLength(10),

                                DatePicker::make('birthday'),

                                TextInput::make('nationality'),

                                Select::make('identity_type')
                                ->options([
                                    'adhaar_card' => 'Adhaar Card',
                                    'payment_receipt' => 'Payment Receipt',
                                    'travel_auhtorization' => 'Travel Auhtorization',
                                    'cwt_document' => 'CWT Document',
                                    'carnet_de_ext' => 'Carnet De Ext.',
                                    'fund_certificate' => 'Fund Certificate'
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
                                        'application/pdf'
                                    ])
                                    ->maxSize(2048)
                                    ->downloadable()
                                    ->openable()
                                // Toggle::make('vip_status')
                                //     ->label('VIP Guest'),

                            ])->columns(3),

                        Tab::make('Preferences')
                            ->schema([

                                Section::make()
                                ->relationship('preferences')

                                ->schema([
                                    Textarea::make('room_preferences'),
                                    Textarea::make('dietary_restrictions'),
                                    Textarea::make('notes'),
                                ]),

                            ]),

                        // Tab::make('Notes')
                        //     ->schema([

                        //         Section::make()
                        //         ->relationship('notes')

                        //         ->schema([
                        //             Textarea::make('note'),
                        //         ]),

                        //     ])

                    ])

            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE (Guest List)
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
    
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('last_stay_date')
                    ->date(),

                // IconColumn::make('vip_status')
                //     ->boolean(),

                // TextColumn::make('loyalty_status')
                //     ->badge(),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | VIEW PAGE (PROFILE TABS)
    |--------------------------------------------------------------------------
    */

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('Guest Profile')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal Information')
                            ->schema([
                                TextEntry::make('first_name'),

                                TextEntry::make('last_name'),

                                TextEntry::make('email'),

                                TextEntry::make('phone'),

                                TextEntry::make('birthday'),

                                TextEntry::make('nationality'),

                                TextEntry::make('identity_type')
                                ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                                TextEntry::make('identity_number'),

                                TextEntry::make('identity_expiry'),

                                ImageEntry::make('identity_document')
                                    ->label('Identity Document')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->openUrlInNewTab()

                            ])->columns(3),

                        Tab::make('Preferences')
                            ->schema([

                                TextEntry::make('preferences.room_preferences'),

                                TextEntry::make('preferences.dietary_restrictions'),

                                TextEntry::make('preferences.notes'),

                            ]),

                        Tab::make('Loyalty Program')
                            ->schema([

                                TextEntry::make('loyalty.points'),

                                TextEntry::make('loyalty.tier'),

                            ]),

                    ])

            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RELATION MANAGERS
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [
            ReservationsRelationManager::class,
            NotesRelationManager::class,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListGuests::route('/'),

            'create' => Pages\CreateGuest::route('/create'),

            'view' => Pages\ViewGuest::route('/{record}'),

            'edit' => Pages\EditGuest::route('/{record}/edit'),

        ];
    }
}