<?php

namespace App\Services;

use App\Enums\HousekeepingTaskStatus;
use App\Enums\HousekeepingTaskType;
use App\Models\HotelRoom;
use App\Models\HousekeepingTask;
use App\Models\Reservation;

class HousekeepingTaskService
{
    public function createCheckoutCleaningTask(HotelRoom $room, ?Reservation $reservation = null): HousekeepingTask
    {
        return HousekeepingTask::query()->create([
            'hotel_id' => $room->hotel_id,
            'hotel_room_id' => $room->id,
            'reservation_id' => $reservation?->id,
            'task_type' => HousekeepingTaskType::CheckoutCleaning,
            'status' => HousekeepingTaskStatus::Pending,
            'priority' => 'normal',
            'due_at' => now()->addHours(2),
            'created_by_id' => auth()->id(),
        ]);
    }
}
