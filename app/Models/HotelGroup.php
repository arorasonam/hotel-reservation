<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelGroup extends Model
{
    // Add this line to allow mass assignment
    protected $fillable = [
        'name',
    ];

    /**
     * Relationship: One Group has many Hotel Admins (Users)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relationship: One Group has many Hotels
     */
    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}
