<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum HotelDescriptionTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    case ADDITIONAL = 'additional';
    case AMENITY = 'amenity';
    case ACTIVITY = 'activity';
    case RESTAURANT = 'restaurant';
    case ROOM = 'room';
    case GENERAL = 'general';
    case POOL = 'pool';
    case LOCATION = 'location';
    case HOW_TO_GET = 'how_to_get';

    public function label(): string
    {
        return match ($this) {
            self::ADDITIONAL   => 'Additional',
            self::AMENITY      => 'Amenity',
            self::ACTIVITY     => 'Activity',
            self::RESTAURANT   => 'Restaurant',
            self::ROOM         => 'Room',
            self::GENERAL      => 'General',
            self::POOL         => 'Pool',
            self::LOCATION     => 'Location',
            self::HOW_TO_GET   => 'How to Get',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADDITIONAL   => 'gray',
            self::AMENITY      => 'success',
            self::ACTIVITY     => 'warning',
            self::RESTAURANT   => 'info',
            self::ROOM         => 'primary',
            self::GENERAL      => 'gray',
            self::POOL         => 'info',
            self::LOCATION     => 'primary',
            self::HOW_TO_GET   => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ADDITIONAL   => 'phosphor-plus',
            self::AMENITY      => 'phosphor-sparkle',
            self::ACTIVITY     => 'phosphor-bicycle',
            self::RESTAURANT   => 'phosphor-fork-knife',
            self::ROOM         => 'phosphor-bed',
            self::GENERAL      => 'phosphor-info',
            self::POOL         => 'phosphor-swimming-pool',
            self::LOCATION     => 'phosphor-map-pin',
            self::HOW_TO_GET   => 'phosphor-navigation-arrow',
        };
    }

    public static function options(): array
    {
        return [
            self::ADDITIONAL->value   => self::ADDITIONAL->label(),
            self::AMENITY->value      => self::AMENITY->label(),
            self::ACTIVITY->value     => self::ACTIVITY->label(),
            self::RESTAURANT->value   => self::RESTAURANT->label(),
            self::ROOM->value         => self::ROOM->label(),
            self::GENERAL->value      => self::GENERAL->label(),
            self::POOL->value         => self::POOL->label(),
            self::LOCATION->value     => self::LOCATION->label(),
            self::HOW_TO_GET->value   => self::HOW_TO_GET->label(),
        ];
    }
}
