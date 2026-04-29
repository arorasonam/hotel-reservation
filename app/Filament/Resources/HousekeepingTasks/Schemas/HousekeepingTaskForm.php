<?php

namespace App\Filament\Resources\HousekeepingTasks\Schemas;

use App\Enums\HousekeepingTaskStatus;
use App\Enums\HousekeepingTaskType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HousekeepingTaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('hotel_id')
                    ->relationship('hotel', 'name')
                    ->required(),
                TextInput::make('hotel_room_id')
                    ->tel()
                    ->required(),
                Select::make('reservation_id')
                    ->relationship('reservation', 'title')
                    ->default(null),
                Select::make('assigned_to_id')
                    ->relationship('assignedTo', 'name')
                    ->default(null),
                Select::make('created_by_id')
                    ->relationship('createdBy', 'name')
                    ->default(null),
                Select::make('task_type')
                    ->options(HousekeepingTaskType::class)
                    ->required(),
                Select::make('status')
                    ->options(HousekeepingTaskStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('priority')
                    ->required()
                    ->default('normal'),
                DateTimePicker::make('due_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
