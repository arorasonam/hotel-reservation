<?php

namespace App\Filament\Resources\HotelGroups;

use App\Filament\Resources\HotelGroups\Pages\CreateHotelGroup;
use App\Filament\Resources\HotelGroups\Pages\EditHotelGroup;
use App\Filament\Resources\HotelGroups\Pages\ListHotelGroups;
use App\Filament\Resources\HotelGroups\Schemas\HotelGroupForm;
use App\Filament\Resources\HotelGroups\Tables\HotelGroupsTable;
use App\Models\HotelGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HotelGroupResource extends Resource
{
    protected static ?string $model = HotelGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'HotelGroup';

    protected static ?string $navigationLabel = 'Hotel Groups';

    protected static ?int $navigationSort = 1;

    protected static UnitEnum|string|null $navigationGroup = 'Hotel Admin Management';

    public static function form(Schema $schema): Schema
    {
        return HotelGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotelGroups::route('/'),
            'create' => CreateHotelGroup::route('/create'),
            'edit' => EditHotelGroup::route('/{record}/edit'),
        ];
    }
}
