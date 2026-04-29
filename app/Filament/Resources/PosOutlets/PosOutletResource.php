<?php

namespace App\Filament\Resources\PosOutlets;

use App\Filament\Resources\PosOutlets\Pages\CreatePosOutlet;
use App\Filament\Resources\PosOutlets\Pages\EditPosOutlet;
use App\Filament\Resources\PosOutlets\Pages\ListPosOutlets;
use App\Helpers\HotelContext;
use App\Models\PosOutlet;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PosOutletResource extends Resource
{
    protected static ?string $model = PosOutlet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'POS Outlet';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (HotelContext::isFiltering()) {
            $query->where('hotel_id', HotelContext::selectedId());
        }

        $user = auth()->user();
        // If bartender, show related outlet data //
        if ($user->hasRole('bartender')) {
            $query->where('id', $user->pos_outlet_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('hotel_id')
                    ->label('Hotel')
                    ->relationship(
                        name: 'hotel',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->orderBy('name')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->required(),

                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true),

                Textarea::make('description'),

                Toggle::make('status')
                    ->default(true),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->searchable(),

                TextColumn::make('hotel.name')
                    ->label('Hotel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code'),

                IconColumn::make('status')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime(),

            ])
            ->filters([
                TernaryFilter::make('status'),
            ]);
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
            'index' => ListPosOutlets::route('/'),
            'create' => CreatePosOutlet::route('/create'),
            'edit' => EditPosOutlet::route('/{record}/edit'),
        ];
    }
}
