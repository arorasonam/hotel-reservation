<?php

namespace App\Filament\Resources\Amenities;

use App\Filament\Resources\Amenities\Pages\ManageAmenities;
use App\Models\Amenity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use UnitEnum;

class AmenityResource extends Resource
{
    protected static ?string $model = Amenity::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Amenities';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Amenity';

    protected static ?string $pluralModelLabel = 'Amenities';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-star')
                    ->placeholder('Enter amenity name'),
                TextInput::make('slug')
                    ->nullable()
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-link')
                    ->placeholder('Auto-generated from name'),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID')
                    ->icon('heroicon-o-hashtag'),
                TextEntry::make('name')
                    ->icon('heroicon-o-star'),
                TextEntry::make('slug')
                    ->icon('heroicon-o-link'),
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-star')
                    ->weight('medium')
                    ->description(fn(Amenity $record): string => $record->slug ?? 'No slug'),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-link')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
                TextColumn::make('hotels_count')
                    ->label('Hotels')
                    ->counts('hotels')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-building-office-2'),
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
                SelectFilter::make('hotels')->label('Hotels')->relationship('hotels', 'name')->multiple()->preload()->searchable(),
                DateRangeFilter::make('created_at'),
                DateRangeFilter::make('updated_at'),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('View')->color('info'),
                EditAction::make()->iconButton()->tooltip('Edit')->modalWidth('md')->color('primary'),
                DeleteAction::make()->iconButton()->tooltip('Delete')->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No amenities yet')
            ->emptyStateDescription('Create your first amenity to get started.')
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAmenities::route('/'),
        ];
    }
}
