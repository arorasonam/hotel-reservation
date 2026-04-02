<?php

namespace App\Filament\Resources\RoomTypes;

use App\Filament\Resources\RoomTypes\Pages\ManageRoomTypes;
use App\Models\RoomType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use UnitEnum;

class RoomTypeResource extends Resource
{
    protected static ?string $model = RoomType::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Room Types';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Room Type';

    protected static ?string $pluralModelLabel = 'Room Types';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon('heroicon-o-tag')
                    ->placeholder('e.g. DLR, EXE, STE')
                    ->hint('Short unique identifier')
                    ->dehydrateStateUsing(fn($state) => strtoupper($state)),

                TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->prefixIcon('heroicon-o-rectangle-stack')
                    ->placeholder('e.g. Deluxe Room'),

                TextArea::make('short_description')
                    ->nullable()
                    ->maxLength(255)
                    ->hintIcon('heroicon-o-bars-3-bottom-left') // Places icon in the top-right hint area
                    ->placeholder('Brief description of this room type'),

                Select::make('bed_type')
                    ->required()
                    ->prefixIcon('heroicon-o-home')
                    ->options([
                        'single'   => 'Single',
                        'double'   => 'Double',
                        'twin'     => 'Twin',
                        'queen'    => 'Queen',
                        'king'     => 'King',
                        'bunk'     => 'Bunk',
                        'sofa_bed' => 'Sofa Bed',
                    ])
                    ->default('double'),

                TextInput::make('num_beds')
                    ->label('Number of Beds')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(10)
                    ->prefixIcon('heroicon-o-hashtag'),

                TextInput::make('max_adults')
                    ->label('Max Adults')
                    ->required()
                    ->numeric()
                    ->default(2)
                    ->minValue(1)
                    ->prefixIcon('heroicon-o-user'),

                TextInput::make('max_children')
                    ->label('Max Children')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->prefixIcon('heroicon-o-user'),

                TextInput::make('max_infants')
                    ->label('Max Infants')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->prefixIcon('heroicon-o-user'),

                Toggle::make('extra_bed_allowed')
                    ->label('Extra Bed Allowed')
                    ->default(false)
                    ->live(),

                TextInput::make('max_extra_beds')
                    ->label('Max Extra Beds')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(5)
                    ->prefixIcon('heroicon-o-hashtag')
                    ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get) => $get('extra_bed_allowed')),

                TextInput::make('default_size_sqft')
                    ->label('Size (sq ft)')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->prefixIcon('heroicon-o-square-2-stack')
                    ->suffix('sq ft'),

                TextInput::make('default_size_sqm')
                    ->label('Size (sq m)')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->prefixIcon('heroicon-o-square-2-stack')
                    ->suffix('sq m'),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->prefixIcon('heroicon-o-arrows-up-down')
                    ->hint('Lower number appears first'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])->columns(2);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID')
                    ->icon('heroicon-o-hashtag'),

                TextEntry::make('code')
                    ->label('Code')
                    ->icon('heroicon-o-tag')
                    ->badge()
                    ->color('primary'),

                TextEntry::make('name')
                    ->icon('heroicon-o-rectangle-stack'),

                TextEntry::make('short_description')
                    ->label('Short Description')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->placeholder('-'),

                TextEntry::make('bed_type')
                    ->label('Bed Type')
                    ->icon('heroicon-o-home')
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color('gray'),

                TextEntry::make('num_beds')
                    ->label('Number of Beds')
                    ->icon('heroicon-o-hashtag'),

                TextEntry::make('max_adults')
                    ->label('Max Adults')
                    ->icon('heroicon-o-user'),

                TextEntry::make('max_children')
                    ->label('Max Children')
                    ->icon('heroicon-o-user'),

                TextEntry::make('max_infants')
                    ->label('Max Infants')
                    ->icon('heroicon-o-user'),

                IconEntry::make('extra_bed_allowed')
                    ->label('Extra Bed Allowed')
                    ->boolean(),

                TextEntry::make('max_extra_beds')
                    ->label('Max Extra Beds')
                    ->icon('heroicon-o-hashtag')
                    ->placeholder('-'),

                TextEntry::make('default_size_sqft')
                    ->label('Size (sq ft)')
                    ->icon('heroicon-o-square-2-stack')
                    ->suffix(' sq ft')
                    ->placeholder('-'),

                TextEntry::make('default_size_sqm')
                    ->label('Size (sq m)')
                    ->icon('heroicon-o-square-2-stack')
                    ->suffix(' sq m')
                    ->placeholder('-'),

                TextEntry::make('sort_order')
                    ->label('Sort Order')
                    ->icon('heroicon-o-arrows-up-down'),

                IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->icon('heroicon-o-clock'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->icon('heroicon-o-clock'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-rectangle-stack')
                    ->weight('medium')
                    ->description(fn(RoomType $record): string => $record->short_description ?? 'No description'),

                TextColumn::make('bed_type')
                    ->label('Bed Type')
                    ->icon('heroicon-o-home')
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('max_adults')
                    ->label('Max Adults')
                    ->icon('heroicon-o-user')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('default_size_sqft')
                    ->label('Size')
                    ->icon('heroicon-o-square-2-stack')
                    ->suffix(' sqft')
                    ->placeholder('-')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rooms_count')
                    ->label('Rooms')
                    ->counts('rooms')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-building-office-2'),

                IconColumn::make('extra_bed_allowed')
                    ->label('Extra Bed')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),

                TernaryFilter::make('extra_bed_allowed')
                    ->label('Extra Bed Allowed'),

                SelectFilter::make('bed_type')
                    ->label('Bed Type')
                    ->options([
                        'single'   => 'Single',
                        'double'   => 'Double',
                        'twin'     => 'Twin',
                        'queen'    => 'Queen',
                        'king'     => 'King',
                        'bunk'     => 'Bunk',
                        'sofa_bed' => 'Sofa Bed',
                    ]),

                DateRangeFilter::make('created_at'),
                DateRangeFilter::make('updated_at'),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('View')->color('info'),
                EditAction::make()->iconButton()->tooltip('Edit')->color('primary'),
                DeleteAction::make()->iconButton()->tooltip('Delete')->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->striped()
            ->emptyStateHeading('No room types yet')
            ->emptyStateDescription('Create your first room type to get started.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoomTypes::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }
}
