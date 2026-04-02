<?php

namespace App\Filament\Resources\Countries\Relations;

use App\Filament\Resources\States\StateResource;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CountryStatesRelationManager extends RelationManager
{
    protected static string $relationship = 'states';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'States';

    protected static BackedEnum|string|null $icon = 'heroicon-o-map-pin';

    public function form(Schema $schema): Schema
    {
        return StateResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return StateResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->fillForm(fn(): array => [
                        'country_id' => $this->getOwnerRecord()->id,
                    ]),
            ]);
    }
}
