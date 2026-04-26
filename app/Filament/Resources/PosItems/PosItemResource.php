<?php

namespace App\Filament\Resources\PosItems;

use App\Filament\Resources\PosItems\Pages\CreatePosItem;
use App\Filament\Resources\PosItems\Pages\EditPosItem;
use App\Filament\Resources\PosItems\Pages\ListPosItems;
use App\Filament\Resources\PosItems\Tables\PosItemsTable;
use App\Models\PosCategory;
use App\Models\PosItem;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use App\Helpers\HotelContext;
use Illuminate\Database\Eloquent\Builder;

class PosItemResource extends Resource
{
    protected static ?string $model = PosItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'Pos Item';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (HotelContext::isFiltering()) {
               $query->whereHas('category.outlet', function (Builder $q) {
                $q->where('pos_outlets.hotel_id', HotelContext::selectedId());
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pos_outlet_id')
                   ->relationship( 'outlet',
                    'name',
                    modifyQueryUsing: function (Builder $query) {
                        $hotelId = HotelContext::selectedId();

                        if ($hotelId) {
                            $query->where('hotel_id', $hotelId);
                        }
                    })
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('pos_category_id')
                    ->relationship('category', 'name')
                    ->options(function ($livewire) {
                        $outletId = data_get($livewire->data, 'pos_outlet_id');

                        if (! $outletId) {
                            return [];
                        }

                        return PosCategory::where('pos_outlet_id', $outletId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->disabled(fn ($livewire) => empty(data_get($livewire->data, 'pos_outlet_id')))
                    ->reactive()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                Toggle::make('status')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return PosItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosItems::route('/'),
            'create' => CreatePosItem::route('/create'),
            'edit' => EditPosItem::route('/{record}/edit'),
        ];
    }
}
