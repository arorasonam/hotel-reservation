<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\ReservationFolioService;
use App\Support\CurrencyDefaults;
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
        $data['currency_code'] = $data['currency_code'] ?? CurrencyDefaults::codeForHotel($data['hotel_id'] ?? null);
        $data['exchange_rate'] = $data['exchange_rate'] ?? CurrencyDefaults::defaultExchangeRate();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));
    }
}
