<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestPreference extends Model
{
    protected $fillable = [
        'guest_id',
        'room_preferences',
        'dietary_restrictions',
        'notes',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
