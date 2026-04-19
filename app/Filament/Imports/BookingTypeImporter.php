<?php

namespace App\Filament\Imports;

use App\Models\BookingType;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BookingTypeImporter extends Importer
{
    protected static ?string $model = BookingType::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example(['FIT', 'Group', 'MICE', 'SERIES']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean'])
                ->example(['1', '1', '0', '1']),
        ];
    }

    public function resolveRecord(): ?BookingType
    {
        $record = new BookingType();
        $record->hotel_id = $this->options['hotel_id']; // Handle UUID mapping

        return $record;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your booking type import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
