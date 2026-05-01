<?php

namespace App\Filament\Resources\HousekeepingStaff\RelationManagers;

use App\Enums\HousekeepingTaskStatus;
use App\Enums\HousekeepingTaskType;
use App\Models\HousekeepingTask;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssignedHousekeepingTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedHousekeepingTasks';

    protected static ?string $title = 'Assigned Tasks';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Task ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('room.floor')
                    ->label('Floor')
                    ->badge()
                    ->sortable(),

                TextColumn::make('task_type')
                    ->label('Task Type')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->badge(),

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
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
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
            ])
            ->recordActions([
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
                        $record->update([
                            'status' => HousekeepingTaskStatus::InProgress,
                            'started_at' => now(),
                        ]);
                    }),

                Action::make('markCompleted')
                    ->label('Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (HousekeepingTask $record): bool => $record->status !== HousekeepingTaskStatus::Completed)
                    ->action(function (HousekeepingTask $record): void {
                        $record->update([
                            'status' => HousekeepingTaskStatus::Completed,
                            'completed_at' => now(),
                        ]);

                        $record->room?->update([
                            'status' => 'vacant',
                        ]);
                    }),
            ])
            ->defaultSort('due_at')
            ->striped();
    }
}
