<?php

namespace App\Filament\Resources\Hotels\Relations;

use App\Enums\HotelMediaTypeEnum;
use App\Models\HotelRoom;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class HotelMediasRelationManager extends RelationManager
{
    protected static string $relationship = 'medias';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Media Files';
    protected static BackedEnum|string|null $icon = 'heroicon-o-photo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Media Details')
                    ->columns(2)
                    ->schema([
                        Select::make('type')->label('Media Type')->options(HotelMediaTypeEnum::class)->required()->columnSpanFull(),
                        FileUpload::make('url')
                            ->label('Upload Image')
                            ->image()
                            ->disk('public')
                            ->directory('hotels/medias')
                            ->visibility('public')
                            ->columnSpanFull(),
                        Select::make('hotel_room_id')->label('Hotel Room')->options(fn($livewire) => HotelRoom::query()->where('hotel_id', $livewire->ownerRecord->id)
                            ->pluck('name', 'id')
                            ->toArray())
                            ->nullable()
                            ->searchable()->columnSpanFull(),
                    ]),
                Section::make('Current Image')
                    ->schema([
                        ImageEntry::make('url')->label('Image Preview')->url(fn($state) => $state)->openUrlInNewTab()
                    ])->hiddenLabel(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')->label('Type')->searchable()->sortable()->badge()->color('info'),
                TextColumn::make('hotelRoom.name')->label('Room')->placeholder('-')->searchable()->sortable(),
                ImageColumn::make('url')->label('Preview'),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Media Details')
                            ->icon('heroicon-o-photo')
                            ->columns(1)
                            ->schema([
                                TextEntry::make('type')->label('Media Type')->badge(),
                                TextEntry::make('hotelRoom.name')->label('Hotel Room')->badge()->color('info')->placeholder('-'),
                                TextEntry::make('created_at')->label('Created At')->dateTime()->icon('heroicon-o-clock'),
                                TextEntry::make('updated_at')->label('Updated At')->dateTime()->icon('heroicon-o-clock'),
                            ]),

                        Section::make('Preview')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                ImageEntry::make('url')->label('Image Preview')->url(fn($state) => $state)->openUrlInNewTab()
                            ]),
                    ]),
            ]);
    }
}
