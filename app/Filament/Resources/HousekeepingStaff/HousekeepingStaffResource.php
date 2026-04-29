<?php

namespace App\Filament\Resources\HousekeepingStaff;

use App\Filament\Resources\HousekeepingStaff\Pages\CreateHousekeepingStaff;
use App\Filament\Resources\HousekeepingStaff\Pages\EditHousekeepingStaff;
use App\Filament\Resources\HousekeepingStaff\Pages\ListHousekeepingStaff;
use App\Filament\Resources\HousekeepingStaff\RelationManagers\AssignedHousekeepingTasksRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use UnitEnum;

class HousekeepingStaffResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static UnitEnum|string|null $navigationGroup = 'Housekeeping';

    protected static ?string $navigationLabel = 'Staff';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Housekeeping Staff';

    protected static ?string $pluralModelLabel = 'Housekeeping Staff';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:User');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role('housekeeping')
            ->withCount([
                'assignedHousekeepingTasks',
                'assignedHousekeepingTasks as pending_tasks_count' => fn (Builder $query): Builder => $query->whereIn('status', [
                    'pending',
                    'assigned',
                    'in_progress',
                ]),
                'assignedHousekeepingTasks as completed_tasks_count' => fn (Builder $query): Builder => $query->where('status', 'completed'),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('phone')
                    ->tel()
                    ->nullable()
                    ->maxLength(20),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('assigned_housekeeping_tasks_count')
                    ->label('Total Tasks')
                    ->badge()
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('pending_tasks_count')
                    ->label('Open Tasks')
                    ->badge()
                    ->sortable()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('completed_tasks_count')
                    ->label('Completed')
                    ->badge()
                    ->sortable()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_open_tasks')
                    ->label('Has Open Tasks')
                    ->query(fn (Builder $query): Builder => $query->whereHas(
                        'assignedHousekeepingTasks',
                        fn (Builder $query): Builder => $query->whereIn('status', [
                            'pending',
                            'assigned',
                            'in_progress',
                        ])
                    )),

                Filter::make('available')
                    ->label('No Open Tasks')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave(
                        'assignedHousekeepingTasks',
                        fn (Builder $query): Builder => $query->whereIn('status', [
                            'pending',
                            'assigned',
                            'in_progress',
                        ])
                    )),

                Filter::make('has_no_tasks')
                    ->label('No Tasks Assigned')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('assignedHousekeepingTasks')),

                Filter::make('completed_tasks')
                    ->label('Has Completed Tasks')
                    ->query(fn (Builder $query): Builder => $query->whereHas(
                        'assignedHousekeepingTasks',
                        fn (Builder $query): Builder => $query->where('status', 'completed')
                    )),

                DateRangeFilter::make('created_at')
                    ->label('Created Date'),
            ])
            ->recordUrl(fn (User $record): string => static::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            AssignedHousekeepingTasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHousekeepingStaff::route('/'),
            'create' => CreateHousekeepingStaff::route('/create'),
            'edit' => EditHousekeepingStaff::route('/{record}/edit'),
        ];
    }
}
