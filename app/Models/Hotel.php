<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Hotel extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, HasSlug;

    protected $guarded = [];
    protected $casts = [
        'address' => 'json',
        'contact' => 'json',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->ref_id)) {
                $model->ref_id = \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'hotel_amenities');
    }

    public function hotelGroup()
    {
        return $this->belongsTo(HotelGroup::class);
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany(HotelDescription::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function medias()
    {
        return $this->hasMany(HotelMedia::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function locationable()
    {
        return $this->morphTo();
    }
}
