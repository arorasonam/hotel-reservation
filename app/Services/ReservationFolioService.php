<?php

namespace App\Services;

use App\Models\PosOrder;
use App\Models\PosPayment;
use App\Models\Reservation;
use App\Models\ReservationFolio;
use App\Models\ReservationRoom;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReservationFolioService
{
    public function syncReservationStayCharge(Reservation $reservation): void
    {
        $amount = round(((float) $reservation->rate) * ((int) $reservation->nights), 2);

        if ($amount <= 0) {
            $this->deleteEntry('reservation', $reservation->id, 'stay_charge');

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
                max(1, (int) $reservation->nights),
                number_format((float) $reservation->rate, 2, '.', '')
            ),
            amount: $amount,
            type: 'debit',
            entryType: 'charge',
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
        $nights = (int) ($reservationRoom->nights ?: $reservation->nights ?: 1);
        $amount = round($rate * $nights, 2);

        if ($amount <= 0) {
            $this->deleteEntry('reservation_room', $reservationRoom->id, 'stay_charge');

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
        );
    }

    public function syncPosOrderCharges(PosOrder $order): void
    {
        if (! $order->reservation_id || ! $order->reservation_room_id || $order->status === 'cancelled') {
            $this->deleteEntriesForSource('pos_order', $order->id);

            return;
        }

        $reservation = $order->reservation;
        $reservationRoom = $order->reservationRoom;

        if (! $reservation || ! $reservationRoom || ! $reservationRoom->isCheckedIn() || $order->status === 'draft') {
            $this->deleteEntriesForSource('pos_order', $order->id);

            return;
        }

        $postedAt = $order->created_at ?? now();
        $reference = $order->order_number ?: ('POS-'.$order->id);

        $this->syncOrderComponent(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
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
            reservationRoom: $reservationRoom,
            order: $order,
            sourceKey: 'tax',
            amount: (float) $order->tax_amount,
            type: 'debit',
            entryType: 'tax',
            description: 'POS tax - Order #'.$reference,
            postedAt: $postedAt,
            reference: $reference,
        );

        $this->syncOrderComponent(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
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

        if (! $order->reservation_id || ! $order->reservation_room_id || $payment->payment_method === 'room_posting') {
            $this->deleteEntry('pos_payment', $payment->id, 'payment');

            return;
        }

        $reservation = $order->reservation;
        $reservationRoom = $order->reservationRoom;

        if (! $reservation || ! $reservationRoom || ! $reservationRoom->isCheckedIn()) {
            $this->deleteEntry('pos_payment', $payment->id, 'payment');

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
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

    public function summarizeMasterFolio(Reservation $reservation): array
    {
        /** @var Collection<int, ReservationFolio> $entries */
        $entries = $reservation->folios()
            ->whereNull('reservation_room_id')
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
        ReservationRoom $reservationRoom,
        PosOrder $order,
        string $sourceKey,
        float $amount,
        string $type,
        string $entryType,
        string $description,
        mixed $postedAt,
        string $reference,
    ): void {
        if ($amount <= 0) {
            $this->deleteEntry('pos_order', $order->id, $sourceKey);

            return;
        }

        $this->upsertEntry(
            reservation: $reservation,
            reservationRoom: $reservationRoom,
            source: 'pos_order',
            sourceId: $order->id,
            sourceKey: $sourceKey,
            description: $description,
            amount: $amount,
            type: $type,
            entryType: $entryType,
            postedAt: $postedAt,
            reference: $reference,
        );
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
                'description' => $description,
                'reference' => $reference,
                'notes' => $notes,
                'amount' => round($amount, 2),
                'type' => $type,
                'entry_type' => $entryType,
                'posted_at' => $postedAt,
            ],
        );
    }
}
