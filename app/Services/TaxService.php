<?php

namespace App\Services;

use App\Models\PosItem;
use App\Models\PosOrder;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\TaxRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaxService
{
    public const TYPE_GST = 'gst';

    public const TYPE_VAT = 'vat';

    public const APPLIES_TO_ACCOMMODATION = 'accommodation';

    public const APPLIES_TO_RESTAURANT = 'restaurant';

    public const ITEM_CATEGORY_STANDARD_RESTAURANT = 'standard_restaurant';

    /**
     * @return array{taxable_amount: float, tax_amount: float, total_percentage: float, taxes: array<int, array{name: string, type: string, percentage: float, amount: float, rule_id: int|null, tax_id: int|null}>, tax_ids: array<int>, rule_ids: array<int>}
     */
    public function calculateAccommodation(float|int|string|null $tariffPerNight, int $nights = 1): array
    {
        $tariffPerNight = (float) $tariffPerNight;
        $nights = max(1, $nights);

        return $this->calculate(
            appliesTo: self::APPLIES_TO_ACCOMMODATION,
            taxableAmount: round($tariffPerNight * $nights, 2),
            tariffPerNight: $tariffPerNight,
        );
    }

    /**
     * @return array{taxable_amount: float, tax_amount: float, total_percentage: float, taxes: array<int, array{name: string, type: string, percentage: float, amount: float, rule_id: int|null, tax_id: int|null}>, tax_ids: array<int>, rule_ids: array<int>}
     */
    public function calculatePosItem(PosOrder $order, PosItem $item, int|float|string $quantity, int|float|string $price): array
    {
        return $this->calculate(
            appliesTo: self::APPLIES_TO_RESTAURANT,
            taxableAmount: round((float) $price * (float) $quantity, 2),
            tariffPerNight: $this->hotelTariffForOrder($order),
            itemTaxCategory: $item->tax_category ?: self::ITEM_CATEGORY_STANDARD_RESTAURANT,
        );
    }

    public function hotelTariffForOrder(PosOrder $order): ?float
    {
        $order->loadMissing([
            'reservationRoom.reservation',
            'reservationRoomDetail.category.reservation',
            'reservation',
        ]);

        if ($order->reservationRoom) {
            $rate = $order->reservationRoom->rate ?: $order->reservationRoom->reservation?->rate;

            return $rate === null ? null : (float) $rate;
        }

        $detailReservation = $order->reservationRoomDetail?->category?->reservation;

        if ($detailReservation) {
            return (float) $detailReservation->rate;
        }

        if ($order->reservation) {
            return (float) $order->reservation->rate;
        }

        return null;
    }

    public function reservationTariff(Reservation $reservation): float
    {
        return (float) $reservation->rate;
    }

    public function reservationRoomTariff(ReservationRoom $reservationRoom): float
    {
        return (float) ($reservationRoom->rate ?: $reservationRoom->reservation?->rate ?: 0);
    }

    public function taxColumns(array $calculation): array
    {
        $taxes = collect($calculation['taxes'] ?? []);
        $tax = $taxes->count() === 1 ? $taxes->first() : null;

        return [
            'tax_id' => $tax['tax_id'] ?? null,
            'tax_rule_id' => $tax['rule_id'] ?? null,
            'tax_name' => $tax['name'] ?? ($taxes->isNotEmpty() ? 'Multiple taxes' : null),
            'tax_type' => $tax['type'] ?? ($taxes->isNotEmpty() ? 'mixed' : null),
            'tax_percentage' => $tax['percentage'] ?? ($calculation['total_percentage'] ?? null),
            'tax_amount' => $calculation['tax_amount'] ?? 0,
        ];
    }

    /**
     * @return array{taxable_amount: float, tax_amount: float, total_percentage: float, taxes: array<int, array{name: string, type: string, percentage: float, amount: float, rule_id: int|null, tax_id: int|null}>, tax_ids: array<int>, rule_ids: array<int>}
     */
    private function calculate(string $appliesTo, float $taxableAmount, ?float $tariffPerNight = null, ?string $itemTaxCategory = null): array
    {
        $rules = $this->matchingRules($appliesTo, $tariffPerNight, $itemTaxCategory);

        $taxes = $rules
            ->map(function (TaxRule $rule) use ($taxableAmount): array {
                $percentage = (float) $rule->tax->percentage;

                return [
                    'name' => $rule->tax->name,
                    'type' => $rule->tax->type,
                    'percentage' => $percentage,
                    'amount' => round(($taxableAmount * $percentage) / 100, 2),
                    'rule_id' => $rule->id,
                    'tax_id' => $rule->tax_id,
                ];
            })
            ->values();

        return [
            'taxable_amount' => $taxableAmount,
            'tax_amount' => round((float) $taxes->sum('amount'), 2),
            'total_percentage' => round((float) $taxes->sum('percentage'), 2),
            'taxes' => $taxes->all(),
            'tax_ids' => $taxes->pluck('tax_id')->filter()->values()->all(),
            'rule_ids' => $rules->pluck('id')->values()->all(),
        ];
    }

    /**
     * @return Collection<int, TaxRule>
     */
    private function matchingRules(string $appliesTo, ?float $tariffPerNight, ?string $itemTaxCategory): Collection
    {
        return TaxRule::query()
            ->with('tax')
            ->where('status', true)
            ->where('applies_to', $appliesTo)
            ->whereHas('tax', fn (Builder $query): Builder => $query->where('status', true))
            ->when(
                $itemTaxCategory,
                fn (Builder $query): Builder => $query->where(function (Builder $query) use ($itemTaxCategory): void {
                    $query->where('item_tax_category', $itemTaxCategory)
                        ->orWhereNull('item_tax_category');
                }),
                fn (Builder $query): Builder => $query->whereNull('item_tax_category'),
            )
            ->where(function (Builder $query) use ($tariffPerNight): void {
                $query
                    ->where(function (Builder $query) use ($tariffPerNight): void {
                        $query->whereNull('min_tariff')
                            ->orWhere('min_tariff', '<=', (float) $tariffPerNight);
                    })
                    ->where(function (Builder $query) use ($tariffPerNight): void {
                        $query->whereNull('max_tariff')
                            ->orWhere('max_tariff', '>=', (float) $tariffPerNight);
                    });
            })
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (TaxRule $rule): string => $rule->tax->type)
            ->map(fn (Collection $rules): TaxRule => $rules->first())
            ->values();
    }
}
