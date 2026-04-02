<?php

namespace App\Filament\Resources\Countries\Relations;

use App\Filament\Resources\PassportVisas\PassportVisaResource;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CountryVisaRequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'visaRequirements';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Visa Requirements for Entry';

    protected static BackedEnum|string|null $icon = 'heroicon-o-globe-alt';

    public function form(Schema $schema): Schema
    {
        return PassportVisaResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return PassportVisaResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->fillForm(fn(): array => [
                        'visa_country_id' => $this->getOwnerRecord()->id,
                    ]),
            ]);
    }
}
