<?php

namespace App\Services;

use App\Models\PosOrder;
use App\Models\PosPayment;
use App\Models\Reservation;
use App\Models\ReservationFolio;
use App\Models\ReservationRoom;
use App\Models\ReservationRoomDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReservationFolioService
{
    public function __construct(
        private readonly TaxService $taxService,
    ) {}

    public function syncReservationStayCharge(Reservation $reservation): void
    {
        $nights = $this->reservationNights($reservation);
        $taxSnapshot = $this->taxService->calculateAccommodation(
            tariffPerNight: $this->taxService->reservationTariff($reservation),
            nights: $nights,
        );
        $amount = $taxSnapshot['taxable_amount'];

        if ($amount <= 0) {
            $this->deleteEntry('reservation', $reservation->id, 'stay_charge');
            $this->deleteEntry('reservation', $reservation->id, 'stay_tax');

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: null,
            source: 'reservation',
            sourceId: $reservation->id,
            sourceKey: 'stay_charge',
            description: sprintf(
                'Room charge for %s night(s) at %s',
                $nights,
                number_format((float) $reservation->rate, 2, '.', '')
            ),
            amount: $amount,
            type: 'debit',
            entryType: 'charge',
            postedAt: $reservation->check_in ? Carbon::parse($reservation->check_in) : now(),
            reference: $reservation->reservation_number,
            taxCalculation: $taxSnapshot,
        );

        $this->syncAccommodationTaxEntry(
            reservation: $reservation,
            reservationRoom: null,
            source: 'reservation',
            sourceId: $reservation->id,
            sourceKey: 'stay_tax',
            roomLabel: 'Reservation',
            taxCalculation: $taxSnapshot,
            postedAt: $reservation->check_in ? Carbon::parse($reservation->check_in) : now(),
            reference: $reservation->reservation_number,
        );
    }

    public function syncReservationRoomStayCharge(ReservationRoom $reservationRoom): void
    {
        $reservation = $reservationRoom->reservation;

        if (! $reservation) {
            return;
        }

        $rate = (float) ($reservationRoom->rate ?: $reservation->rate);
        $nights = $this->reservationRoomNights($reservationRoom);
        $taxSnapshot = $this->taxService->calculateAccommodation(
            tariffPerNight: $rate,
            nights: $nights,
        );
        $amount = $taxSnapshot['taxable_amount'];

        if ($amount <= 0) {
            $this->deleteEntry('reservation_room', $reservationRoom->id, 'stay_charge');
            $this->deleteEntry('reservation_room', $reservationRoom->id, 'stay_tax');

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
            source: 'reservation_room',
            sourceId: $reservationRoom->id,
            sourceKey: 'stay_charge',
            description: sprintf(
                'Room %s charge for %s night(s) at %s',
                $reservationRoom->room_number ?: 'Auto',
                max(1, $nights),
                number_format($rate, 2, '.', '')
            ),
            amount: $amount,
            type: 'debit',
            entryType: 'charge',
            postedAt: $reservationRoom->check_in ? Carbon::parse($reservationRoom->check_in) : now(),
            reference: $reservation->reservation_number,
            taxCalculation: $taxSnapshot,
        );

        $this->syncAccommodationTaxEntry(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
            source: 'reservation_room',
            sourceId: $reservationRoom->id,
            sourceKey: 'stay_tax',
            roomLabel: 'Room '.($reservationRoom->room_number ?: 'Auto'),
            taxCalculation: $taxSnapshot,
            postedAt: $reservationRoom->check_in ? Carbon::parse($reservationRoom->check_in) : now(),
            reference: $reservation->reservation_number,
        );
    }

    public function syncPosOrderCharges(PosOrder $order): void
    {
        if (! $order->reservation_id || ! $order->reservation_room_detail_id || $order->status === 'cancelled') {
            $this->deleteEntriesForSource('pos_order', $order->id);

            return;
        }

        $reservation = $order->reservation;
        $reservationRoomDetail = $order->reservationRoomDetail;

        // if (! $reservation || ! $reservationRoomDetail || ! $reservationRoomDetail->isCheckedIn() || $order->status === 'draft') {
        if (! $reservation || ! $reservationRoomDetail || $order->status === 'draft') {
            $this->deleteEntriesForSource('pos_order', $order->id);

            return;
        }

        $postedAt = $order->created_at ?? now();
        $reference = $order->order_number ?: ('POS-'.$order->id);

        $this->syncOrderComponent(
            reservation: $reservation,
            reservationRoomDetail: $reservationRoomDetail,
            order: $order,
            sourceKey: 'charge',
            amount: (float) $order->subtotal,
            type: 'debit',
            entryType: 'charge',
            description: 'POS charge - Order #'.$reference,
            postedAt: $postedAt,
            reference: $reference,
        );

        $this->syncOrderComponent(
            reservation: $reservation,
            reservationRoomDetail: $reservationRoomDetail,
            order: $order,
            sourceKey: 'tax',
            amount: (float) $order->tax_amount,
            type: 'debit',
            entryType: 'tax',
            description: 'POS tax - Order #'.$reference,
            postedAt: $postedAt,
            reference: $reference,
            taxCalculation: $this->orderTaxCalculation($order),
        );

        $this->syncOrderComponent(
            reservation: $reservation,
            reservationRoomDetail: $reservationRoomDetail,
            order: $order,
            sourceKey: 'discount',
            amount: (float) $order->discount_amount,
            type: 'credit',
            entryType: 'discount',
            description: 'POS discount - Order #'.$reference,
            postedAt: $postedAt,
            reference: $reference,
        );
    }

    public function deleteEntriesForSource(string $source, int|string $sourceId): void
    {
        ReservationFolio::query()
            ->where('source', $source)
            ->where('source_id', $sourceId)
            ->delete();
    }

    public function syncPosPayment(PosPayment $payment): void
    {
        $order = $payment->order;

        if (! $order) {
            $this->deleteEntry('pos_payment', $payment->id, 'payment');

            return;
        }

        if ($order->status === 'draft') {
            $order->forceFill([
                'status' => 'confirmed',
            ])->save();

            $order->refresh();
        }

        $this->syncPosOrderCharges($order);

        if (! $order->reservation_id || ! $order->reservation_room_detail_id || $payment->payment_method === 'room_posting') {
            $this->deleteEntry('pos_payment', $payment->id, 'payment');

            return;
        }

        $reservation = $order->reservation;
        $reservationRoomDetail = $order->reservationRoomDetail;

        if (! $reservation || ! $reservationRoomDetail || ! $reservationRoomDetail->isCheckedIn()) {
            $this->deleteEntry('pos_payment', $payment->id, 'payment');

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: null,
            source: 'pos_payment',
            sourceId: $payment->id,
            sourceKey: 'payment',
            description: 'POS payment - Order #'.$order->order_number,
            amount: (float) $payment->amount,
            type: 'credit',
            entryType: 'payment',
            postedAt: $payment->paid_at ?? $payment->created_at ?? now(),
            reference: $payment->transaction_reference ?: $order->order_number,
            notes: ucfirst((string) $payment->payment_method),
            reservationRoomDetail: $reservationRoomDetail,
        );
    }

    public function deleteEntry(string $source, int|string $sourceId, string $sourceKey): void
    {
        ReservationFolio::query()
            ->where('source', $source)
            ->where('source_id', $sourceId)
            ->where('source_key', $sourceKey)
            ->delete();
    }

    public function summarize(Reservation $reservation): array
    {
        /** @var Collection<int, ReservationFolio> $entries */
        $entries = $reservation->folios()->get();

        return $this->summarizeEntries($entries);
    }

    public function summarizeReservationRoom(ReservationRoom $reservationRoom): array
    {
        /** @var Collection<int, ReservationFolio> $entries */
        $entries = $reservationRoom->folios()->get();

        return $this->summarizeEntries($entries);
    }

    public function summarizeReservationRoomDetail(ReservationRoomDetail $reservationRoomDetail): array
    {
        /** @var Collection<int, ReservationFolio> $entries */
        $entries = $reservationRoomDetail->folios()->get();

        return $this->summarizeEntries($entries);
    }

    public function summarizeMasterFolio(Reservation $reservation): array
    {
        /** @var Collection<int, ReservationFolio> $entries */
        $entries = $reservation->folios()
            ->whereNull('reservation_room_id')
            ->whereNull('reservation_room_detail_id')
            ->get();

        return $this->summarizeEntries($entries);
    }

    private function summarizeEntries(Collection $entries): array
    {
        $debits = (float) $entries
            ->where('type', 'debit')
            ->sum('amount');

        $credits = (float) $entries
            ->where('type', 'credit')
            ->sum('amount');

        return [
            'debits' => round($debits, 2),
            'credits' => round($credits, 2),
            'balance' => round($debits - $credits, 2),
        ];
    }

    private function syncOrderComponent(
        Reservation $reservation,
        ReservationRoomDetail $reservationRoomDetail,
        PosOrder $order,
        string $sourceKey,
        float $amount,
        string $type,
        string $entryType,
        string $description,
        mixed $postedAt,
        string $reference,
        ?array $taxCalculation = null,
    ): void {
        if ($amount <= 0) {
            $this->deleteEntry('pos_order', $order->id, $sourceKey);

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: null,
            source: 'pos_order',
            sourceId: $order->id,
            sourceKey: $sourceKey,
            description: $description,
            amount: $amount,
            type: $type,
            entryType: $entryType,
            postedAt: $postedAt,
            reference: $reference,
            reservationRoomDetail: $reservationRoomDetail,
            taxCalculation: $taxCalculation,
        );
    }

    private function syncAccommodationTaxEntry(
        Reservation $reservation,
        ?ReservationRoom $reservationRoom,
        string $source,
        int|string $sourceId,
        string $sourceKey,
        string $roomLabel,
        array $taxCalculation,
        mixed $postedAt,
        ?string $reference,
    ): void {
        if ((float) $taxCalculation['tax_amount'] <= 0) {
            $this->deleteEntry($source, $sourceId, $sourceKey);

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
            source: $source,
            sourceId: $sourceId,
            sourceKey: $sourceKey,
            description: sprintf(
                '%s tax at %s%% on %s',
                $roomLabel,
                number_format((float) $taxCalculation['total_percentage'], 2, '.', ''),
                number_format((float) $taxCalculation['taxable_amount'], 2, '.', ''),
            ),
            amount: (float) $taxCalculation['tax_amount'],
            type: 'debit',
            entryType: 'tax',
            postedAt: $postedAt,
            reference: $reference,
            taxCalculation: $taxCalculation,
        );
    }

    private function orderTaxCalculation(PosOrder $order): array
    {
        $items = $order->items()->get();
        $taxes = collect();

        foreach ($items as $item) {
            foreach ($item->tax_breakdown as $tax) {
                $key = implode('|', [
                    $tax['name'] ?? '',
                    $tax['type'] ?? '',
                    $tax['percentage'] ?? '',
                    $tax['tax_id'] ?? '',
                    $tax['rule_id'] ?? '',
                ]);

                $current = $taxes->get($key, [
                    'name' => $tax['name'] ?? 'Tax',
                    'type' => $tax['type'] ?? TaxService::TYPE_GST,
                    'percentage' => (float) ($tax['percentage'] ?? 0),
                    'amount' => 0.00,
                    'rule_id' => $tax['rule_id'] ?? null,
                    'tax_id' => $tax['tax_id'] ?? null,
                ]);

                $current['amount'] = round((float) $current['amount'] + (float) ($tax['amount'] ?? 0), 2);
                $taxes->put($key, $current);
            }
        }

        return [
            'taxable_amount' => (float) $order->subtotal,
            'tax_amount' => (float) $order->tax_amount,
            'total_percentage' => $this->taxPercentageFromAmounts((float) $order->tax_amount, (float) $order->subtotal),
            'taxes' => $taxes->values()->all(),
            'tax_ids' => $taxes->pluck('tax_id')->filter()->values()->all(),
            'rule_ids' => $taxes->pluck('rule_id')->filter()->values()->all(),
        ];
    }

    private function reservationNights(Reservation $reservation): int
    {
        $nights = $reservation->getAttribute('nights')
            ?? $reservation->getRawOriginal('nights')
            ?? Reservation::query()->whereKey($reservation->getKey())->value('nights');

        return max(1, (int) $nights);
    }

    private function reservationRoomNights(ReservationRoom $reservationRoom): int
    {
        $nights = $reservationRoom->getAttribute('nights')
            ?? $reservationRoom->getRawOriginal('nights')
            ?? ReservationRoom::query()->whereKey($reservationRoom->getKey())->value('nights');

        if ($nights) {
            return max(1, (int) $nights);
        }

        return $reservationRoom->reservation
            ? $this->reservationNights($reservationRoom->reservation)
            : 1;
    }

    private function taxPercentageFromAmounts(float $taxAmount, float $taxableAmount): float
    {
        if ($taxableAmount <= 0 || $taxAmount <= 0) {
            return 0.00;
        }

        return round(($taxAmount / $taxableAmount) * 100, 2);
    }

    private function upsertEntry(
        Reservation $reservation,
        ?ReservationRoom $reservationRoom,
        string $source,
        int|string|null $sourceId,
        string $sourceKey,
        string $description,
        float $amount,
        string $type,
        string $entryType,
        mixed $postedAt,
        ?string $reference = null,
        ?string $notes = null,
        ?ReservationRoomDetail $reservationRoomDetail = null,
        ?array $taxCalculation = null,
    ): ReservationFolio {
        return ReservationFolio::query()->updateOrCreate(
            [
                'source' => $source,
                'source_id' => $sourceId,
                'source_key' => $sourceKey,
            ],
            [
                'reservation_id' => $reservation->getKey(),
                'reservation_room_id' => $reservationRoom?->getKey(),
                'reservation_room_detail_id' => $reservationRoomDetail?->getKey(),
                'description' => $description,
                'reference' => $reference,
                'notes' => $notes,
                'amount' => round($amount, 2),
                ...app(TaxService::class)->taxColumns($taxCalculation ?? []),
                'type' => $type,
                'entry_type' => $entryType,
                'posted_at' => $postedAt,
            ],
        );
    }
}
