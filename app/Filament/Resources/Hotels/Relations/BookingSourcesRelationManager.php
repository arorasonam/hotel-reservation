<?php

namespace App\Filament\Resources\Hotels\Relations;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use App\Filament\Imports\BookingSourceImporter; // Path to your importer class
use Filament\Actions\ImportAction;

class BookingSourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'bookingSources';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Booking Sources';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(BookingSourceImporter::class)
                    ->label('Import Sources')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    // This allows the user to download a sample CSV/Excel based on your columns
                    ->options([
                        'hotel_id' => $this->getOwnerRecord()->id,
                    ]),
                CreateAction::make()
                    ->label('New booking source'),
            ])
            ->actions([
                EditAction::make()->iconButton()->color('primary'),
                DeleteAction::make()->iconButton()->color('danger'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Toggle::make('is_active')->default(true),
        ]);
    }
}
