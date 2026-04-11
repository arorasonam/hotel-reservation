<?php

namespace App\Filament\Resources\PosCategories\Pages;

use App\Filament\Resources\PosCategories\PosCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosCategory extends CreateRecord
{
    protected static string $resource = PosCategoryResource::class;
}
