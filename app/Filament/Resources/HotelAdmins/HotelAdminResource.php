<?php

namespace App\Filament\Resources\HotelAdmins;

use App\Filament\Resources\HotelAdmins\Pages\CreateHotelAdmin;
use App\Filament\Resources\HotelAdmins\Pages\EditHotelAdmin;
use App\Filament\Resources\HotelAdmins\Pages\ListHotelAdmins;
use App\Filament\Resources\HotelAdmins\Schemas\HotelAdminForm;
use App\Filament\Resources\HotelAdmins\Tables\HotelAdminsTable;
use App\Models\User as HotelAdmin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class HotelAdminResource extends Resource
{
    protected static ?string $model = HotelAdmin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Hotel Admins';

    protected static UnitEnum|string|null $navigationGroup = 'Hotel Admin Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'hotel_admin');
            });
    }

    public static function form(Schema $schema): Schema
    {
        return HotelAdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelAdminsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHotelAdmins::route('/'),
            'create' => CreateHotelAdmin::route('/create'),
            'edit'   => EditHotelAdmin::route('/{record}/edit'),
        ];
    }
}
