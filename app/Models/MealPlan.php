<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class MealPlan extends Model
{
    protected $table = 'meal_plans';
    protected $guarded = [];

    // public function reservationRooms()
    // {
    //     return $this->hasMany(ReservationRoom::class, 'meal_plan_id');
    // }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
}
