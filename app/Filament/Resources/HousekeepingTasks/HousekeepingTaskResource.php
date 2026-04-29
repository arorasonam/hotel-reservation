<?php

namespace App\Filament\Resources\HousekeepingTasks;

use App\Enums\HousekeepingTaskStatus;
use App\Enums\HousekeepingTaskType;
use App\Filament\Resources\HousekeepingTasks\Pages\CreateHousekeepingTask;
use App\Filament\Resources\HousekeepingTasks\Pages\EditHousekeepingTask;
use App\Filament\Resources\HousekeepingTasks\Pages\ListHousekeepingTasks;
use App\Models\HousekeepingTask;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class HousekeepingTaskResource extends Resource
{
    protected static ?string $model = HousekeepingTask::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Housekeeping';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->can('ViewAny:HousekeepingTask')
            || $user?->can('View:HousekeepingTask')
            || $user?->hasRole('housekeeping'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('hotel_id')
                    ->relationship('hotel', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('hotel_room_id')
                    ->label('Room')
                    ->relationship('room', 'room_number')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('task_type')
                    ->options(collect(HousekeepingTaskType::cases())->mapWithKeys(
                        fn (HousekeepingTaskType $type): array => [$type->value => $type->label()]
                    ))
                    ->required(),

                Select::make('assigned_to_id')
                    ->label('Assigned To')
                    ->relationship(
                        'assignedTo',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->role('housekeeping')
                    )
                    ->searchable()
                    ->preload(),

                Select::make('status')
                    ->options(collect(HousekeepingTaskStatus::cases())->mapWithKeys(
                        fn (HousekeepingTaskStatus $status): array => [$status->value => $status->label()]
                    ))
                    ->default(HousekeepingTaskStatus::Pending->value)
                    ->required(),

                Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->default('normal')
                    ->required(),

                DateTimePicker::make('due_at'),

                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Task ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('room.room_number')
                    ->label('Room Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('task_type')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->badge(),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->searchable(),

                TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->badge()
                    ->color(fn ($state): string => $state?->color() ?? 'gray'),

                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'normal' => 'info',
                        'low' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn (HousekeepingTask $record): string => static::getUrl('edit', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    Action::make('assign')
                        ->label('Assign')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Select::make('assigned_to_id')
                                ->label('Housekeeping Staff')
                                ->relationship(
                                    'assignedTo',
                                    'name',
                                    modifyQueryUsing: fn ($query) => $query->role('housekeeping')
                                )
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (HousekeepingTask $record, array $data): void {
                            $record->update([
                                'assigned_to_id' => $data['assigned_to_id'],
                                'status' => HousekeepingTaskStatus::Assigned,
                            ]);
                        }),

                    Action::make('markInProgress')
                        ->label('In Progress')
                        ->icon('heroicon-o-play')
                        ->color('warning')
                        ->visible(fn (HousekeepingTask $record): bool => ! in_array($record->status, [
                            HousekeepingTaskStatus::InProgress,
                            HousekeepingTaskStatus::Completed,
                            HousekeepingTaskStatus::Cancelled,
                        ], true))
                        ->action(function (HousekeepingTask $record): void {
                            static::updateTaskStatus($record, HousekeepingTaskStatus::InProgress);
                        }),

                    Action::make('markCompleted')
                        ->label('Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (HousekeepingTask $record): bool => $record->status !== HousekeepingTaskStatus::Completed)
                        ->action(function (HousekeepingTask $record): void {
                            static::updateTaskStatus($record, HousekeepingTaskStatus::Completed);
                        }),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Actions'),

            ])
            ->bulkActions([
                BulkAction::make('markCompleted')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            static::updateTaskStatus($record, HousekeepingTaskStatus::Completed);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('changePriority')
                    ->label('Change Priority')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->form([
                        Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required(),
                    ])
                    ->action(function ($records, array $data): void {
                        foreach ($records as $record) {
                            $record->update([
                                'priority' => $data['priority'],
                            ]);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('changeStatus')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('status')
                            ->options(collect(HousekeepingTaskStatus::cases())->mapWithKeys(
                                fn (HousekeepingTaskStatus $status): array => [$status->value => $status->label()]
                            ))
                            ->required(),
                    ])
                    ->action(function ($records, array $data): void {
                        $status = HousekeepingTaskStatus::from($data['status']);

                        foreach ($records as $record) {
                            static::updateTaskStatus($record, $status);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('assignSelected')
                    ->label('Assign Selected')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('assigned_to_id')
                            ->label('Housekeeping Staff')
                            ->relationship(
                                'assignedTo',
                                'name',
                                modifyQueryUsing: fn ($query) => $query->role('housekeeping')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function ($records, array $data): void {
                        foreach ($records as $record) {
                            $record->update([
                                'assigned_to_id' => $data['assigned_to_id'],
                                'status' => HousekeepingTaskStatus::Assigned,
                            ]);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(HousekeepingTaskStatus::cases())->mapWithKeys(
                        fn (HousekeepingTaskStatus $status): array => [$status->value => $status->label()]
                    )),

                SelectFilter::make('task_type')
                    ->options(collect(HousekeepingTaskType::cases())->mapWithKeys(
                        fn (HousekeepingTaskType $type): array => [$type->value => $type->label()]
                    )),

                SelectFilter::make('assigned_to_id')
                    ->label('Assigned Staff')
                    ->relationship(
                        'assignedTo',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->role('housekeeping')
                    ),

                SelectFilter::make('hotel_room_id')
                    ->label('Room')
                    ->relationship('room', 'room_number'),

                Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now())
                        ->whereNot('status', HousekeepingTaskStatus::Completed->value)),
            ])
            ->poll('10s')
            ->defaultSort('due_at', 'asc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHousekeepingTasks::route('/'),
            'create' => CreateHousekeepingTask::route('/create'),
            'edit' => EditHousekeepingTask::route('/{record}/edit'),
        ];
    }

    protected static function updateTaskStatus(HousekeepingTask $record, HousekeepingTaskStatus $status): void
    {
        $updates = [
            'status' => $status,
        ];

        if ($status === HousekeepingTaskStatus::InProgress) {
            $updates['started_at'] = $record->started_at ?? now();
        }

        if ($status === HousekeepingTaskStatus::Completed) {
            $updates['started_at'] = $record->started_at ?? now();
            $updates['completed_at'] = now();
        }

        $record->update($updates);

        if ($status === HousekeepingTaskStatus::Completed) {
            $record->room?->update([
                'status' => 'vacant',
            ]);
        }
    }
}
