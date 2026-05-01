<?php

namespace App\Helpers;

use App\Models\Hotel;

class HotelContext
{
    public static function selectedId(): ?string
    {
        return session('selected_hotel_id') 
            ?? Hotel::orderBy('name')->value('id');
    }

    public static function selected(): ?Hotel
    {
        $id = static::selectedId();
        return $id ? Hotel::find($id) : null;
    }

    public static function isFiltering(): bool
    {
        return !is_null(static::selectedId());
    }
}