<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationRoom extends Model
{
    protected $table = 'reservation_rooms';
    protected $guarded = [];

    protected $fillable = [
        'reservation_id',
        'room_type_id',
        'meal_plan_id',
        'room_number', // Stores 'Auto' or specific number
        'adults',
        'children',
        'infant',
        'status', // For partial check-in logic
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }
}
