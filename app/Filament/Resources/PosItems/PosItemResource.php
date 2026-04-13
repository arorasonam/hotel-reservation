<?php

namespace App\Filament\Resources\PosItems;

use App\Filament\Resources\PosItems\Pages\CreatePosItem;
use App\Filament\Resources\PosItems\Pages\EditPosItem;
use App\Filament\Resources\PosItems\Pages\ListPosItems;
use App\Filament\Resources\PosItems\Schemas\PosItemForm;
use App\Filament\Resources\PosItems\Tables\PosItemsTable;
use App\Models\PosItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use UnitEnum;

class PosItemResource extends Resource
{
    protected static ?string $model = PosItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $recordTitleAttribute = 'Pos Item';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pos_outlet_id')
                    ->relationship('outlet', 'name')
                    ->required(),

                Select::make('pos_category_id')
                    ->relationship('category', 'name')
                    ->required(),

                TextInput::make('name')
                    ->required(),

                TextInput::make('price')
                    ->numeric()
                    ->required(),

                TextInput::make('tax_percentage')
                    ->numeric()
                    ->default(0),

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
        return [
            //
        ];
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
