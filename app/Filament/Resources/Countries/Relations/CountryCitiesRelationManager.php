<?php

namespace App\Filament\Resources\Countries\Relations;

use App\Filament\Resources\Cities\CityResource;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;

class CountryCitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'cities';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Cities';

    protected static BackedEnum|string|null $icon = 'heroicon-o-building-office-2';

    public function form(Schema $schema): Schema
    {
        return CityResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CityResource::table($table);
    }
}
