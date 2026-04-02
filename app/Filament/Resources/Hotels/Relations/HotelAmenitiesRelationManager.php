<?php

namespace App\Filament\Resources\Hotels\Relations;

use App\Filament\Resources\Amenities\AmenityResource;
use BackedEnum;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HotelAmenitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'amenities';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Amenities';
    protected static BackedEnum|string|null $icon = 'heroicon-o-star';

    public function infolist(Schema $schema): Schema
    {
        return AmenityResource::infolist($schema);
    }

    public function table(Table $table): Table
    {
        return AmenityResource::table($table)
            ->headerActions([
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make()->iconButton()->tooltip('Detach')->color('danger'),
            ]);
    }
}
