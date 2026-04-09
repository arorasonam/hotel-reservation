<?php

namespace App\Filament\Resources\Hotels\Relations;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;

class HotelRoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'rooms';

    // Updated to use Room Number as the primary title
    protected static ?string $recordTitleAttribute = 'room_number';

    protected static ?string $title = 'Rooms';

    protected static BackedEnum|string|null $icon = 'heroicon-o-home';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Room Details')
                    ->schema([
                        // This Grid ensures fields take up 2 columns instead of stacking
                        Grid::make(2)
                            ->schema([
                                TextInput::make('room_number')
                                    ->label('Room Number')
                                    ->required()
                                    ->placeholder('e.g., 211'),

                                Select::make('room_type_id')
                                    ->label('Room Type')
                                    ->relationship('roomType', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                                TextInput::make('floor')
                                    ->label('Floor')
                                    ->required()
                                    ->placeholder('e.g., 2'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'vacant' => 'Vacant',
                                        'occupied' => 'Occupied',
                                        'dirty' => 'Dirty',
                                        'maint_blk' => 'Maintenance Block',
                                    ])
                                    ->default('vacant')
                                    ->required()
                                    ->native(false), // Makes the dropdown look cleaner
                            ]),

                        Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true)
                            ->inline(false), // Moves the label next to the toggle
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_number')
                    ->label('Room No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('roomType.name')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('floor')
                    ->label('Floor')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->color(fn(string $state): string => match ($state) {
                        'vacant' => 'success',
                        'occupied' => 'danger',
                        'dirty' => 'warning',
                        'maint_blk' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),

                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),

                ImageColumn::make('medias.url')
                    ->label('Media')
                    ->circular()
                    ->stacked()
                    ->placeholder('No Image'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                  Action::make('changeStatus')
                    ->label('Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('secondary')
                    ->form([
                        Select::make('status')
                            ->options([
                                'vacant' => 'Vacant',
                                'occupied' => 'Occupied',
                                'dirty' => 'Dirty',
                                'maint_blk' => 'Maintenance Block',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                        ]);
                    }),
                ViewAction::make()->iconButton()->tooltip('View')->color('secondary'),
                EditAction::make()->iconButton()->tooltip('Edit')->color('primary'),
                DeleteAction::make()->iconButton()->tooltip('Delete')->color('danger'),

            ])->bulkActions([
                    BulkActionGroup::make([
                        BulkAction::make('deleteSelected')
                            ->label('Delete Selected')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(fn ($records) => $records->each->delete()),

                        BulkAction::make('changeStatus')
                            ->label('Change Status')
                            ->form([
                                Select::make('status')
                                    ->label('Select Status')
                                    ->options([
                                        'vacant' => 'Vacant',
                                    'occupied' => 'Occupied',
                                    'dirty' => 'Dirty',
                                    'maint_blk' => 'Maintenance Block',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => $data['status'],
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                 ])
            ])->filters([
                SelectFilter::make('floor')
                    ->label('Floor')
                    ->options(fn () => 
                        \App\Models\HotelRoom::query()
                            ->where('hotel_id', $this->getOwnerRecord()->id)
                            ->distinct()
                            ->pluck('floor', 'floor')
                            ->toArray()
                    ),

                SelectFilter::make('room_type_id')
                    ->label('Room Type')
                    ->relationship('roomType', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'vacant' => 'Vacant',
                        'occupied' => 'Occupied',
                        'dirty' => 'Dirty',
                        'maint_blk' => 'Maintenance Block',
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Room Information')
                            ->icon('heroicon-o-home')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('room_number')->label('Room Number')->weight('bold'),
                                TextEntry::make('roomType.name')->label('Room Type'),
                                TextEntry::make('floor')->label('Floor')->badge(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'vacant' => 'success',
                                        'occupied' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('is_visible')
                                    ->label('Visible')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'danger'),
                            ]),
                    ]),
            ]);
    }
}
