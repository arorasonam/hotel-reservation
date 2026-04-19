<?php

namespace App\Filament\Imports;

use App\Models\BookingSource;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BookingSourceImporter extends Importer
{
    protected static ?string $model = BookingSource::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example(['Direct', 'OTA', 'GDS']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean'])
                ->example(['1', '1', '0']),
        ];
    }

    public function resolveRecord(): ?BookingSource
    {
        $record = new BookingSource();
        $record->hotel_id = $this->options['hotel_id']; // Handle UUID mapping

        return $record;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your booking source import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
