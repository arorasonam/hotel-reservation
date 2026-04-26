<?php

namespace App\Filament\Resources\InventoryItems;

use App\Filament\Resources\InventoryItems\Pages\CreateInventoryItem;
use App\Filament\Resources\InventoryItems\Pages\EditInventoryItem;
use App\Filament\Resources\InventoryItems\Pages\ListInventoryItems;
use App\Filament\Resources\InventoryItems\Schemas\InventoryItemForm;
use App\Filament\Resources\InventoryItems\Tables\InventoryItemsTable;
use App\Models\InventoryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Models\InventoryTransaction;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Inventory Item';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hotel_id'),
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('unit')
                ->label('Unit (kg, pcs, ml)')
                ->required(),

            TextInput::make('current_stock')
                ->numeric()
                ->default(0)
                ->required(),

            TextInput::make('cost_price')
                ->numeric()
                ->default(0),

            TextInput::make('reorder_level')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
       return $table
            ->columns([
                TextColumn::make('name')->searchable(),

                TextColumn::make('unit'),

                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->sortable()
                    ->label('Stock')
                    ->badge()
                    ->color(function ($record) {

                        if ($record->current_stock <= 0) {
                            return 'danger'; // Red
                        }

                        if ($record->current_stock <= $record->reorder_level) {
                            return 'warning'; // Yellow
                        }

                        return 'success'; // Green
                    }),

                TextColumn::make('reorder_level'),

                TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->actions([

                EditAction::make(),

                // ADD STOCK ACTION
                Action::make('addStock')
                    ->label('Add Stock')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->label('Quantity to Add'),
                    ])
                    ->action(function ($record, array $data) {

                        $record->increment('current_stock', $data['quantity']);

                        InventoryTransaction::create([
                            'inventory_item_id' => $record->id,
                            'type' => 'PURCHASE',
                            'quantity' => $data['quantity'],
                        ]);
                    }),

                //REDUCE STOCK (MANUAL ADJUSTMENT)
                Action::make('reduceStock')
                    ->label('Reduce Stock')
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->label('Quantity to Reduce'),
                    ])
                    ->action(function ($record, array $data) {

                        $record->decrement('current_stock', $data['quantity']);

                        InventoryTransaction::create([
                            'inventory_item_id' => $record->id,
                            'type' => 'ADJUSTMENT',
                            'quantity' => -$data['quantity'],
                        ]);
                    }),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\InventoryItems\RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryItems::route('/'),
            'create' => CreateInventoryItem::route('/create'),
            'edit' => EditInventoryItem::route('/{record}/edit'),
        ];
    }
}
