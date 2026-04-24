<?php

namespace App\Filament\Resources\Hotels;

use App\Filament\Resources\Hotels\Pages\CreateHotel;
use App\Filament\Resources\Hotels\Pages\EditHotel;
use App\Filament\Resources\Hotels\Pages\ListHotels;
use App\Filament\Resources\Hotels\Pages\ViewHotel;
use App\Filament\Resources\Hotels\Schemas\HotelForm;
use App\Filament\Resources\Hotels\Schemas\HotelInfolist;
use App\Filament\Resources\Hotels\Tables\HotelsTable;
use App\Models\Hotel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use App\Filament\Resources\Hotels\Relations\HotelDescriptionsRelationManager;
use App\Filament\Resources\Hotels\Relations\HotelAmenitiesRelationManager;
use App\Filament\Resources\Hotels\Relations\HotelRoomsRelationManager;
use App\Filament\Resources\Hotels\Relations\HotelMediasRelationManager;
use App\Filament\Resources\Hotels\Relations\BookingSourcesRelationManager;
use App\Filament\Resources\Hotels\Relations\SourceMarketsRelationManager;
use App\Filament\Resources\Hotels\Relations\BookingTypesRelationManager;
use App\Filament\Resources\Hotels\Relations\MealPlansRelationManager;
use Illuminate\Database\Eloquent\Builder;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Hotels';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Hotel';

    protected static ?string $pluralModelLabel = 'Hotels';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user->hasRole('super_admin')) {
            return $query;                                          // all hotels
        }

        return $query->where('hotel_group_id', $user->hotel_group_id);

        return $query->whereRaw('1 = 0');                          // no access
    }

    public static function form(Schema $schema): Schema
    {
        return HotelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HotelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HotelDescriptionsRelationManager::class,
            HotelAmenitiesRelationManager::class,
            HotelRoomsRelationManager::class,
            HotelMediasRelationManager::class,
            BookingSourcesRelationManager::class,
            SourceMarketsRelationManager::class,
            BookingTypesRelationManager::class,
            MealPlansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotels::route('/'),
            'create' => CreateHotel::route('/create'),
            'view' => ViewHotel::route('/{record}'),
            'edit' => EditHotel::route('/{record}/edit'),
        ];
    }
}
