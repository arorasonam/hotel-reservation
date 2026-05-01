<?php

namespace App\Enums;

enum HousekeepingTaskType: string
{
    case CheckoutCleaning = 'checkout_cleaning';
    case StayoverCleaning = 'stayover_cleaning';
    case DeepCleaning = 'deep_cleaning';
    case Inspection = 'inspection';
    case Laundry = 'laundry';
    case MaintenanceRequest = 'maintenance_request';

    public function label(): string
    {
        return match ($this) {
            self::CheckoutCleaning => 'Checkout Cleaning',
            self::StayoverCleaning => 'Stayover Cleaning',
            self::DeepCleaning => 'Deep Cleaning',
            self::Inspection => 'Inspection',
            self::Laundry => 'Laundry',
            self::MaintenanceRequest => 'Maintenance Request',
        };
    }
}
