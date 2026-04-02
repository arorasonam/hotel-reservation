<?php

namespace App\Models;

use App\Enums\HotelDescriptionTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelDescription extends Model
{
    use HasUuids;
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'type' => HotelDescriptionTypeEnum::class,
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
