<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = [
        'timezones' => 'json',
    ];


    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function hotels()
    {
        return $this->hasManyThrough(Hotel::class, City::class, 'country_id', 'locationable_id')
            ->where('locationable_type', City::class)
            ->union(
                $this->hasManyThrough(Hotel::class, State::class, 'country_id', 'locationable_id')->where('locationable_type', State::class)
            );
    }

    public function getRouteKeyName()
    {
        return 'iso2';
    }
}
