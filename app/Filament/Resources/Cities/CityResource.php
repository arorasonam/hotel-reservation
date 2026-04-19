<?php

namespace App\Filament\Resources\Cities;

use App\Filament\Resources\Cities\Pages\CreateCity;
use App\Filament\Resources\Cities\Pages\EditCity;
use App\Filament\Resources\Cities\Pages\ListCities;
use App\Filament\Resources\Cities\Pages\ViewCity;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\States\StateResource;
use App\Filament\Resources\Cities\Relations\CityQuestionMatchLocationsRelationManager;
use App\Filament\Resources\Cities\Relations\CityTagsRelationManager;
use App\Filament\Resources\Cities\Relations\CityActivitiesRelationManager;
use App\Models\City;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Cities';

    protected static UnitEnum|string|null $navigationGroup = 'Location Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'City';

    protected static ?string $pluralModelLabel = 'Cities';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('City Information')
                    ->description('Enter the city details and location')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->placeholder('Enter city name')
                            ->columnSpan(2),
                        TextInput::make('natural_name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-language')
                            ->placeholder('Enter natural name')
                            ->columnSpan(2),
                        Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->emoji . ' ' . $record->name)
                            ->prefixIcon('heroicon-o-globe-alt'),
                        Select::make('state_id')
                            ->label('State')
                            ->relationship('state', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-map-pin'),
                        TextInput::make('state_code')
                            ->nullable()
                            ->maxLength(10)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->placeholder('State code'),
                        TextInput::make('slug')
                            ->nullable()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('Auto-generated from name'),
                    ]),

                Section::make('Geographic Coordinates')
                    ->description('Latitude and longitude information')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->nullable()
                            ->numeric()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Enter latitude'),
                        TextInput::make('longitude')
                            ->nullable()
                            ->numeric()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Enter longitude'),
                    ]),

                Section::make('Additional Information')
                    ->description('Reference data')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(1)
                    ->schema([
                        TextInput::make('ref_id')
                            ->label('Reference ID')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null)
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('External reference ID')
                            ->helperText('Cannot be changed after creation'),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('City Details')
                    ->description('City information and location')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->icon('heroicon-o-building-office-2')
                            ->size('lg')
                            ->weight('bold')
                            ->color('primary')
                            ->columnSpan(2),
                        TextEntry::make('id')
                            ->label('ID')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('ID copied!')
                            ->copyMessageDuration(1500),
                        TextEntry::make('natural_name')
                            ->icon('heroicon-o-language')
                            ->columnSpan(2),
                        TextEntry::make('state_code')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                    ]),

                Section::make('Location Information')
                    ->description('Country and state details')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('country.name')
                            ->label('Country')
                            ->icon('heroicon-o-globe-alt')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($record) => $record->country->emoji . ' ' . $record->country->name)
                            ->url(fn($record) => CountryResource::getUrl('view', ['record' => $record->country]))
                            ->openUrlInNewTab(false),
                        TextEntry::make('state.name')
                            ->label('State')
                            ->icon('heroicon-o-map-pin')
                            ->badge()
                            ->color('success')
                            ->placeholder('No state')
                            ->url(fn($record) => $record->state ? StateResource::getUrl('view', ['record' => $record->state]) : null)
                            ->openUrlInNewTab(false),
                    ]),

                Section::make('Geographic Coordinates')
                    ->description('Latitude and longitude')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('latitude')
                            ->icon('heroicon-o-map-pin')
                            ->badge()
                            ->color('success')
                            ->placeholder('-'),
                        TextEntry::make('longitude')
                            ->icon('heroicon-o-map-pin')
                            ->badge()
                            ->color('success')
                            ->placeholder('-'),
                    ]),

                Section::make('Additional Details')
                    ->description('Slug and reference information')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('slug')
                            ->icon('heroicon-o-link')
                            ->placeholder('No slug')
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('Slug copied!')
                            ->copyMessageDuration(1500),
                        TextEntry::make('ref_id')
                            ->label('Reference ID')
                            ->numeric()
                            ->icon('heroicon-o-link')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('Reference ID copied!')
                            ->copyMessageDuration(1500),
                    ]),

                Section::make('Timestamps')
                    ->description('Record creation and update times')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-clock')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->icon('heroicon-o-clock')
                            ->placeholder('-'),
                    ]),
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->weight('mwum')
                    ->description(fn($record): string => $record->natural_name ?? 'No natural name'),
                TextColumn::make('country.iso2')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-flag')
                    ->formatStateUsing(fn($record) => $record->country->emoji . ' ' . $record->country->iso2)
                    ->url(fn($record) => CountryResource::getUrl('view', ['record' => $record->country]))
                    ->openUrlInNewTab(false),
                TextColumn::make('state.name')
                    ->label('State')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-map-pin')
                    ->placeholder('No state')
                    ->url(fn($record) => $record->state ? StateResource::getUrl('view', ['record' => $record->state]) : null)
                    ->openUrlInNewTab(false)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('state_code')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latitude')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('longitude')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-link')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
                TextColumn::make('ref_id')
                    ->label('Ref ID')
                    ->numeric()
                    ->sortable()
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
                SelectFilter::make('country')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->emoji . ' ' . $record->name),

                SelectFilter::make('state')
                    ->label('State')
                    ->relationship('state', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View')
                    ->color('info'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit')
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->defaultSort('name', 'asc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCities::route('/'),
            'create' => CreateCity::route('/create'),
            'view' => ViewCity::route('/{record}'),
            'edit' => EditCity::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CityQuestionMatchLocationsRelationManager::class,
            CityTagsRelationManager::class,
            CityActivitiesRelationManager::class,
        ];
    }
}
