<?php

namespace App\Filament\Imports;

use App\Models\MealPlan;
use App\Models\RoomType;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class MealPlanImporter extends Importer
{
    protected static ?string $model = MealPlan::class;

    public static function getColumns(): array
    {
        return [
            // Use the relationship but specify the title column for the lookup
            ImportColumn::make('room_type_name')
                ->label('Room Type Name') // This is the column header in the CSV
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->example(['Superior Room', 'Deluxe Room']),

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example(['Breakfast Included', 'Room Only']),

            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example(['CP', 'EP']),

            ImportColumn::make('extra_charge')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric'])
                ->example(['500', '0']),

            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean'])
                ->example(['1', '0']),
        ];
    }

    public function resolveRecord(): ?MealPlan
    {
        // 1. Manually resolve the Room Type within the current hotel's scope
        // This prevents the importer from picking a 'Deluxe Room' from a different hotel
        $roomType = RoomType::where('name', $this->data['room_type_name'])->first();
        if (!$roomType) {
            return null;
        }

        // 2. Map the attributes correctly
        return MealPlan::firstOrNew([
            'hotel_id' => $this->options['hotel_id'],
            'room_type_id' => $roomType->id,
            'code' => $this->data['code'],
        ], [
            'name' => $this->data['name'],
            'extra_charge' => $this->data['extra_charge'],
            'is_active' => $this->data['is_active'] ?? true,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your meal plan import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
