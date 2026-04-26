<?php

namespace App\Filament\Resources\PosCategories;

use App\Filament\Resources\PosCategories\Pages\CreatePosCategory;
use App\Filament\Resources\PosCategories\Pages\EditPosCategory;
use App\Filament\Resources\PosCategories\Pages\ListPosCategories;
use App\Models\PosCategory;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
use App\Helpers\HotelContext;
use Illuminate\Database\Eloquent\Builder;

class PosCategoryResource extends Resource
{
    protected static ?string $model = PosCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'Pos Category';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (HotelContext::isFiltering()) {
             $query->whereHas('outlet', function (Builder $q) {
                $q->where('hotel_id', HotelContext::selectedId());
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
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('tax_id')
                    ->relationship('tax', 'name')
                    ->label('Category Tax')
                    ->searchable()
                    ->preload(),
                Toggle::make('status')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet'),
                TextColumn::make('name'),
                TextColumn::make('tax.name')
                    ->label('Tax')
                    ->placeholder('No tax'),
                IconColumn::make('status')
                    ->boolean(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosCategories::route('/'),
            'create' => CreatePosCategory::route('/create'),
            'edit' => EditPosCategory::route('/{record}/edit'),
        ];
    }
}
