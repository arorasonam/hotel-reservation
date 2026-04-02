<?php

namespace App\Filament\Resources\Hotels\Relations;

use App\Enums\HotelDescriptionTypeEnum;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HotelDescriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'descriptions';

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $title = 'Descriptions';

    protected static BackedEnum|string|null $icon = 'heroicon-o-document-text';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')->label('Description')->limit(120)->wrap()->searchable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('View')->color('info'),
                EditAction::make()->iconButton()->tooltip('Edit')->color('primary'),
                DeleteAction::make()->iconButton()->tooltip('Delete')->color('danger'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')->label('Type')->options(HotelDescriptionTypeEnum::class)->required(),
                Textarea::make('content')->label('Description')->required()->rows(10)->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type')->label('Type')->badge()->color('primary'),
                TextEntry::make('content')->label('Description')->wrap()->columnSpanFull(),
            ]);
    }
}
