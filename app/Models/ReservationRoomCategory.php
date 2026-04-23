<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationRoomCategory extends Model
{
    protected $table = 'reservation_room_categories';

    protected $guarded = [];

    protected $fillable = [
        'reservation_id',
        'room_type_id',
        'meal_plan_id',
        'rooms_count',
    ];

    public function roomDetails(): HasMany
    {
        return $this->hasMany(ReservationRoomDetail::class, 'category_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }
}
