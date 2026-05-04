<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\ReservationFolioService;
use App\Support\CurrencyDefaults;
use App\Support\MoneyConverter;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosOrder extends EditRecord
{
    protected static string $resource = PosOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['base_currency_code'] = MoneyConverter::baseCurrencyForHotel($data['hotel_id'] ?? null);
        $data['currency_code'] = data_get($data, 'selected_currency_code')
            ?? data_get($this->data, 'selected_currency_code')
            ?? data_get($data, 'currency_code')
            ?? data_get($this->data, 'currency_code')
            ?? CurrencyDefaults::codeForHotel($data['hotel_id'] ?? null);
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

    protected function afterSave(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));
    }
}
