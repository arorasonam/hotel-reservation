<?php

namespace App\Filament\Resources\Countries\Relations;

use App\Filament\Resources\Tags\TagResource;
use BackedEnum;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountryTagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Tags';

    protected static BackedEnum|string|null $icon = 'heroicon-o-tag';

    public function table(Table $table): Table
    {
        return TagResource::table($table)
            ->headerActions([
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ]);
    }
}
