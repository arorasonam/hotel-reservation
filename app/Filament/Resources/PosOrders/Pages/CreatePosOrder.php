<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\CurrencyService;
use App\Services\ReservationFolioService;
use Filament\Resources\Pages\CreateRecord;

class CreatePosOrder extends CreateRecord
{
    protected static string $resource = PosOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $this->data['items'] ?? [];

        $subtotal = 0;
        $taxAmount = 0;

        $currencyCode = $data['currency_code'] ?? 'INR';
        $rate = app(CurrencyService::class)->getRate($currencyCode);

        foreach ($items as $index => $item) {
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);

            $itemSubtotal = $price * $qty;
            $itemTax = (float) ($item['tax_amount'] ?? 0);

            $subtotal += $itemSubtotal;
            $taxAmount += $itemTax;

            // Update item-level base values
            $items[$index]['base_price'] = app(CurrencyService::class)->toBase($price, $rate);
            $items[$index]['base_total'] = app(CurrencyService::class)->toBase($itemSubtotal, $rate);

            // Save currency info at item level
            $items[$index]['currency_code'] = $currencyCode;
            $items[$index]['exchange_rate_used'] = $rate;
        }

        $discount = (float) ($data['discount_amount'] ?? 0);
        $grandTotal = ($subtotal + $taxAmount) - $discount;

        // Original values (as-is)

        $data['subtotal'] = $subtotal;
        $data['grand_total'] = $grandTotal;

        // Base values (INR)
        $data['exchange_rate_used'] = $rate;
        $data['base_subtotal'] = app(CurrencyService::class)->toBase($subtotal, $rate);
        $data['base_discount_amount'] = app(CurrencyService::class)->toBase($discount, $rate);
        $data['base_tax_amount'] = app(CurrencyService::class)->toBase($taxAmount, $rate);
        $data['base_grand_total'] = app(CurrencyService::class)->toBase($grandTotal, $rate);

        // Currency info
        $data['currency_code'] = $currencyCode;
        $data['exchange_rate_used'] = $rate;

        // Assign updated items back
        $data['items'] = $items;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));
    }
}
