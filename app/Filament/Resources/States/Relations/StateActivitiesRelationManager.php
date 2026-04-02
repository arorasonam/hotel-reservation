<?php

namespace App\Filament\Resources\States\Relations;

use App\Filament\Resources\Activities\ActivityResource;
use BackedEnum;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StateActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Activities';

    protected static BackedEnum|string|null $icon = 'heroicon-o-sparkles';

    public function table(Table $table): Table
    {
        return ActivityResource::table($table)
            ->headerActions([
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
