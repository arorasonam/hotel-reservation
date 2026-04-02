<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'short_description',
        'long_description',
        'bed_type',
        'num_beds',
        'max_adults',
        'max_children',
        'max_infants',
        'extra_bed_allowed',
        'max_extra_beds',
        'default_size_sqft',
        'default_size_sqm',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'extra_bed_allowed' => 'boolean',
        'is_active'         => 'boolean',
    ];

    // Rooms that reference this type (across all hotels)
    public function rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }

    // Scope: only active types
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Label used in dropdowns
    public function getFullLabelAttribute(): string
    {
        return "{$this->code} — {$this->name}";
    }
}
