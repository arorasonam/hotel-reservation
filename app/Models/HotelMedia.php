<?php

namespace App\Models;

use App\Enums\HotelMediaTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HotelMedia extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = [
        'type' => HotelMediaTypeEnum::class,
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    protected function url(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }
                return Storage::disk('public')->url($value);
            },
        );
    }
}
