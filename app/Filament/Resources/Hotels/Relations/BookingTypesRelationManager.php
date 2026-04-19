<?php

namespace App\Filament\Resources\Hotels\Relations;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Imports\BookingTypeImporter; // Path to your importer class
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Toggle;

class BookingTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'bookingTypes';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Booking Types';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->badge()->color('success')->searchable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(BookingTypeImporter::class)
                    ->label('Import Sources')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    // This allows the user to download a sample CSV/Excel based on your columns
                    ->options([
                        'hotel_id' => $this->getOwnerRecord()->id,
                    ]),
                CreateAction::make()
                    ->label('New booking type'),
            ])
            ->actions([
                EditAction::make()->iconButton()->color('primary'),
                DeleteAction::make()->iconButton()->color('danger'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->placeholder('e.g., FIT, MICE, GROUP')
                ->required()
                ->maxLength(255),
            Toggle::make('is_active')->default(true),
        ]);
    }
}
