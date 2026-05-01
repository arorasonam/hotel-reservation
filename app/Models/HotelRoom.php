<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelRoom extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public $timestamps = false;

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function medias()
    {
        return $this->hasMany(HotelMedia::class);
    }

    /**
     * Get the type/category that owns the room.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class, 'hotel_room_id');
    }
}
