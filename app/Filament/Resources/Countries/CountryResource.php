<?php

namespace App\Filament\Resources\Countries;

use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\Countries\Pages\ListCountries;
use App\Filament\Resources\Countries\Pages\ViewCountry;
use App\Filament\Resources\Countries\Relations\CountryCitiesRelationManager;
use App\Filament\Resources\Countries\Relations\CountryPassportVisasRelationManager;
use App\Filament\Resources\Countries\Relations\CountryStatesRelationManager;
use App\Filament\Resources\Countries\Relations\CountryVisaRequirementsRelationManager;
use App\Filament\Resources\Countries\Relations\CountryQuestionMatchLocationsRelationManager;
use App\Filament\Resources\Countries\Relations\CountryTagsRelationManager;
use App\Filament\Resources\Countries\Relations\CountryActivitiesRelationManager;
use App\Filament\Resources\Countries\Schemas\CountryForm;
use App\Filament\Resources\Countries\Schemas\CountryInfolist;
use App\Filament\Resources\Countries\Tables\CountriesTable;
use App\Models\Country;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Countries';

    protected static UnitEnum|string|null $navigationGroup = 'Location Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Country';

    protected static ?string $pluralModelLabel = 'Countries';

    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CountryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CountryStatesRelationManager::class,
            CountryCitiesRelationManager::class,
            CountryPassportVisasRelationManager::class,
            CountryVisaRequirementsRelationManager::class,
            CountryQuestionMatchLocationsRelationManager::class,
            CountryTagsRelationManager::class,
            CountryActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'view' => ViewCountry::route('/{record}'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
