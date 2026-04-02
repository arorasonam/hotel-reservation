<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum HotelMediaTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    case HOTEL = 'hotel';
    case ROOM = 'room';
    case SERVICE = 'service';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::HOTEL => 'Hotel',
            self::ROOM => 'Room',
            self::SERVICE => 'Service',
            self::GENERAL => 'General',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HOTEL => 'primary',
            self::ROOM => 'info',
            self::SERVICE => 'success',
            self::GENERAL => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::HOTEL => 'heroicon-o-building-office-2',
            self::ROOM => 'heroicon-o-home',
            self::SERVICE => 'heroicon-o-wrench-screwdriver',
            self::GENERAL => 'heroicon-o-document',
        };
    }
}
