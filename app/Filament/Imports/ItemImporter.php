<?php

namespace App\Filament\Imports;

use App\Models\PosItem;
use App\Models\PosOutlet;
use App\Models\PosCategory;
use App\Models\Tax;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use App\Helpers\HotelContext;

class ItemImporter extends Importer
{
    protected static ?string $model = PosItem::class;
    protected ?string $hotelId = null;

    public function mount(): void
    {
         if (HotelContext::isFiltering()) {
            $this->hotelId = HotelContext::selectedId();
        }

        // $this->hotelId = $this->data['hotel_id'] ?? null;
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('outlet_name')
                ->label('Outlet Name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            
            ImportColumn::make('outlet_code')
                ->label('Outlet COde')
                ->requiredMapping()
                ->rules(['required', 'string']),
            
            ImportColumn::make('outlet_description')
                ->label('Outlet Description')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('outlet_status')
                ->label('Outlet Status')
                ->requiredMapping()
                ->rules(['required', 'integer']),

            ImportColumn::make('category_name')
                ->label('Category Name')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('category_tax')
                ->label('Category Tax')
                ->requiredMapping()
                ->rules(['required']),
            
            ImportColumn::make('category_status')
                ->label('Category Status')
                ->requiredMapping()
                ->rules(['required', 'integer']),

            ImportColumn::make('name')
                ->label('Item Name')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('price')
                ->label('Item Price')
                ->numeric()
                ->rules(['nullable', 'numeric']),

            ImportColumn::make('status')
                ->label('Item Status')
                ->requiredMapping()
                ->rules(['required', 'integer']),

            ImportColumn::make('description'),
        ];
    }

    public static function getChunkSize(): int
    {
        return 100; // process 100 rows at a time
    }

    public static function shouldQueue(): bool
    {
        return false; // run in background queue
    }

    public function resolveRecord(): PosItem
    {
        $outlet = $this->getOutlet();
        $category = $this->getCategory($outlet);

        return PosItem::firstOrNew([
            'name' => $this->data['name'],
            'pos_outlet_id' => $outlet->id,
            'pos_category_id' => $category->id,
            'price' => $this->data['price'],
            'status' => $this->data['status'],
        ]);
    }

    public function fillRecord(): void
    {
        try {
            $outlet = $this->getOutlet();
            $category = $this->getCategory($outlet);
            
            $this->record->pos_outlet_id   = $outlet->id;
            $this->record->pos_category_id = $category->id;
            $this->record->name        = $this->data['name'];
            $this->record->price       = $this->data['price'];
            $this->record->status      = $this->data['status'];

        } catch (\Exception $e) {
            \Log::error('Import Error: ' . $e->getMessage(), $this->data);
            throw $e;
        }
    }

    protected function getOutlet(): PosOutlet
    {
        return PosOutlet::firstOrCreate(
            [
                'code' => $this->data['outlet_code'],
                'hotel_id' => $this->hotelId,
                'name' => $this->data['outlet_name'] ?? $this->data['outlet_code'],
                'description' => $this->data['outlet_description'],
                'status' => $this->data['outlet_status'],
            ]
            );
    }

    protected function getCategory(PosOutlet $outlet): PosCategory
    {
        $tax = $this->getTax();

        return PosCategory::firstOrCreate([
            'name' => $this->data['category_name'],
            'pos_outlet_id' => $outlet->id,
            'tax_id' => $tax?->id,
            'status' => $this->data['category_status']
        ]);
    }

    protected function getTax(): ?Tax
    {
        if (empty($this->data['category_tax'])) {
            return null;
        }

        return Tax::where('name', $this->data['category_tax'])->first();
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        return "Import completed. 
            Success: {$import->successful_rows}, 
            Failed: {$import->failed_rows}";
    }
}