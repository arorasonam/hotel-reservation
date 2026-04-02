<?php

namespace App\Filament\Resources\States;

use App\Filament\Resources\States\Pages\CreateState;
use App\Filament\Resources\States\Pages\EditState;
use App\Filament\Resources\States\Pages\ListStates;
use App\Filament\Resources\States\Pages\ViewState;
use App\Filament\Resources\States\Relations\StateCitiesRelationManager;
use App\Filament\Resources\States\Relations\StateQuestionMatchLocationsRelationManager;
use App\Filament\Resources\States\Relations\StateTagsRelationManager;
use App\Filament\Resources\States\Relations\StateActivitiesRelationManager;
use App\Filament\Resources\States\Schemas\StateForm;
use App\Filament\Resources\States\Schemas\StateInfolist;
use App\Filament\Resources\States\Tables\StatesTable;
use App\Models\State;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'States';

    protected static UnitEnum|string|null $navigationGroup = 'Location Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'State';

    protected static ?string $pluralModelLabel = 'States';

    public static function form(Schema $schema): Schema
    {
        return StateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StateCitiesRelationManager::class,
            StateQuestionMatchLocationsRelationManager::class,
            StateTagsRelationManager::class,
            StateActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStates::route('/'),
            'create' => CreateState::route('/create'),
            'view' => ViewState::route('/{record}'),
            'edit' => EditState::route('/{record}/edit'),
        ];
    }
}
