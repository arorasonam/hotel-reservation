<?php

namespace App\Filament\Resources\Cities\Relations;

use App\Filament\Resources\QuestionMatchLocations\QuestionMatchLocationResource;
use App\Models\City;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CityQuestionMatchLocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'questionMatchLocations';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Match Locations';

    protected static BackedEnum|string|null $icon = 'heroicon-o-map';

    public function form(Schema $schema): Schema
    {
        return QuestionMatchLocationResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return QuestionMatchLocationResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->fillForm(fn(): array => [
                        'locationable_id' => $this->getOwnerRecord()->id,
                        'locationable_type' => City::class,
                    ]),
            ]);
    }
}
