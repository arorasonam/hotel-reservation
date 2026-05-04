<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\ReservationFolioService;
use App\Support\MoneyConverter;
use Filament\Resources\Pages\CreateRecord;

class CreatePosOrder extends CreateRecord
{
    protected static string $resource = PosOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $this->data['items'] ?? [];

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $itemTax = (float) ($item['tax_amount'] ?? 0);

            $subtotal += $itemSubtotal;
            $taxAmount += $itemTax;
        }

        $discount = (float) ($data['discount_amount'] ?? 0);
        $grandTotal = $subtotal + $taxAmount - $discount;

        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = $taxAmount;
        $data['grand_total'] = $grandTotal;
        $data['created_by'] = auth()->id();
        $data['base_currency_code'] = MoneyConverter::baseCurrencyForHotel($data['hotel_id'] ?? null);
        $data['currency_code'] = data_get($data, 'selected_currency_code')
            ?? data_get($this->data, 'selected_currency_code')
            ?? data_get($data, 'currency_code')
            ?? data_get($this->data, 'currency_code')
            ?? $data['base_currency_code'];
        $data['exchange_rate'] = data_get($data, 'selected_exchange_rate')
            ?? data_get($this->data, 'selected_exchange_rate')
            ?? data_get($data, 'exchange_rate')
            ?? data_get($this->data, 'exchange_rate');

        unset($data['selected_currency_code'], $data['selected_exchange_rate']);

        if (
            $data['currency_code'] !== $data['base_currency_code']
            && (blank($data['exchange_rate'] ?? null) || (float) $data['exchange_rate'] === 1.0)
        ) {
            $data['exchange_rate'] = MoneyConverter::exchangeRateToBase($data['currency_code'], $data['base_currency_code']);
        }

        $data['exchange_rate'] = $data['exchange_rate'] ?? MoneyConverter::exchangeRateToBase($data['currency_code'], $data['base_currency_code']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));
    }
}
