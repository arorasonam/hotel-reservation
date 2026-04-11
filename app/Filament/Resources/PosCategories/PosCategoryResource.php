<?php

namespace App\Filament\Resources\PosCategories;

use App\Filament\Resources\PosCategories\Pages\CreatePosCategory;
use App\Filament\Resources\PosCategories\Pages\EditPosCategory;
use App\Filament\Resources\PosCategories\Pages\ListPosCategories;
use App\Filament\Resources\PosCategories\Schemas\PosCategoryForm;
use App\Filament\Resources\PosCategories\Tables\PosCategoriesTable;
use App\Models\PosCategory;
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

class PosCategoryResource extends Resource
{
    protected static ?string $model = PosCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Pos Category';

     public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pos_outlet_id')
                ->relationship('outlet', 'name')
                ->required(),

            TextInput::make('name')
                ->required(),

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

                IconColumn::make('status')->boolean(),
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
            'index' => ListPosCategories::route('/'),
            'create' => CreatePosCategory::route('/create'),
            'edit' => EditPosCategory::route('/{record}/edit'),
        ];
    }
}
