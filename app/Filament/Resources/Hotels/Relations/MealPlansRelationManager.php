<?php

namespace App\Filament\Resources\Hotels\Relations;

use App\Models\RoomType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Imports\MealPlanImporter; // Path to your importer class
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Toggle;


class MealPlansRelationManager extends RelationManager
{
    protected static string $relationship = 'mealPlans';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Meal Plans & Rates';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                // Show which Category this meal plan belongs to
                TextColumn::make('roomType.name')
                    ->label('Category')
                    ->sortable()
                    ->badge(),
                TextColumn::make('name')->label('Plan Name')->searchable(),
                TextColumn::make('code')->badge()->color('info'),
                TextColumn::make('extra_charge')
                    ->label('Price/Charge')
                    ->money('INR')
                    ->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(MealPlanImporter::class)
                    ->label('Import Meal Plans')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    // This allows the user to download a sample CSV/Excel based on your columns
                    ->options([
                        'hotel_id' => $this->getOwnerRecord()->id,
                    ]),
                CreateAction::make()
                    ->label('New meal plan'),
            ])
            ->actions([
                EditAction::make()->iconButton()->color('primary'),
                DeleteAction::make()->iconButton()->color('danger'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                // Link the Meal Plan to a Room Type
                Select::make('room_type_id')
                    ->label('Category')
                    ->options(
                        fn($livewire) =>
                        RoomType::where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->placeholder('e.g., Breakfast Included')
                    ->required(),

                TextInput::make('code')
                    ->placeholder('e.g., CP, MAP')
                    ->required(),

                TextInput::make('extra_charge')
                    ->label('Additional Charge')
                    ->numeric()
                    ->prefix('₹')
                    ->default(0),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }
}
