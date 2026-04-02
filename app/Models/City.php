<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class City extends Model
{
    use HasUuids, HasSlug;
    protected $guarded = [];

    public function state()
    {
        return $this->belongsTo(State::class);
    }


    public function hotels()
    {
        return $this->morphMany(Hotel::class, 'locationable');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom(['country.iso2', 'name'])->saveSlugsTo('slug');
    }

    public function activities()
    {
        return $this->morphToMany(Activity::class, 'cityable', 'city_activities');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'cityable', 'city_tags');
    }
    public function tripPlans()
    {
        return $this->morphToMany(TripPlan::class, 'cityable', 'trip_plan_cities');
    }
    public function questionMatchLocations()
    {
        return $this->morphMany(QuestionMatchLocation::class, 'locationable');
    }
}
