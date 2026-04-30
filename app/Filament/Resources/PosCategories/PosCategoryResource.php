<?php

namespace App\Filament\Resources\PosCategories;

use App\Filament\Resources\PosCategories\Pages\CreatePosCategory;
use App\Filament\Resources\PosCategories\Pages\EditPosCategory;
use App\Filament\Resources\PosCategories\Pages\ListPosCategories;
use App\Helpers\HotelContext;
use App\Models\Country;
use App\Models\PosCategory;
use App\Models\PosOutlet;
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
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

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

        $user = auth()->user();
        // If bartender, show related outlet data //
        if ($user->hasRole('bartender')) {
            $query->where('pos_outlet_id', $user->pos_outlet_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pos_outlet_id')
                    ->label('Outlet')
                    ->relationship(
                        'outlet',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereIn('id', self::getFilteredOutletQuery()->pluck('id'))
                    )
                    ->default(fn () => ($ids = self::getFilteredOutletQuery()->pluck('id'))->count() === 1
                            ? $ids->first()
                            : null
                    )
                    ->disabled(fn () => self::getFilteredOutletQuery()->count() === 1
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set): void {
                        $set('taxes', []);
                    })
                    ->required()
                    ->dehydrated(true),
                TextInput::make('name')
                    ->required(),
                Select::make('taxes')
                    ->relationship(
                        'taxes',
                        'name',
                        modifyQueryUsing: fn (Builder $query, callable $get) => self::scopeTaxesToOutletCountry($query, $get('pos_outlet_id'))
                    )
                    ->label('Category Taxes')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Toggle::make('status')
                    ->default(true),
            ]);
    }

    protected static function getFilteredOutletQuery(): Builder
    {
        $query = PosOutlet::query();

        if ($hotelId = HotelContext::selectedId()) {
            $query->where('hotel_id', $hotelId);
        }

        $user = auth()->user();

        if ($user->hasRole('bartender')) {
            $query->where('id', $user->pos_outlet_id);
        }

        return $query;
    }

    protected static function scopeTaxesToOutletCountry(Builder $query, mixed $outletId): Builder
    {
        $location = PosOutlet::query()
            ->with('hotel.locationable')
            ->find($outletId)
            ?->hotel
            ?->locationable;

        $countryId = $location instanceof Country ? $location->id : $location?->country_id;

        return $query
            ->where('status', true)
            ->when($countryId, fn (Builder $query): Builder => $query->where('country_id', $countryId));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet'),
                TextColumn::make('name'),
                TextColumn::make('taxes.name')
                    ->label('Taxes')
                    ->badge()
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
