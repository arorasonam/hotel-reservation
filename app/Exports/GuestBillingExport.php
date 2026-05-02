<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GuestBillingExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        private string $dateFrom,
        private string $dateTo,
        private ?string $outletId = null,
    ) {}

    public function collection()
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $query = PosOrder::where(function ($query) use ($from, $to): void {
            $query->whereBetween('pos_orders.settled_at', [$from, $to])
                ->orWhere(function ($query) use ($from, $to): void {
                    $query->whereNull('pos_orders.settled_at')
                        ->whereBetween('pos_orders.created_at', [$from, $to]);
                });
        })
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
            ->where('pos_orders.order_type', 'room_charge')
            ->leftJoin('reservation_guests', function (JoinClause $join): void {
                $join->on('pos_orders.guest_id', '=', 'reservation_guests.guest_id')
                    ->on('pos_orders.reservation_id', '=', 'reservation_guests.reservation_id');
            })
            ->leftJoin('guests', 'pos_orders.guest_id', '=', 'guests.id')
            ->join('reservations', 'pos_orders.reservation_id', '=', 'reservations.id')
            ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
            ->selectRaw('
                TRIM(CONCAT(COALESCE(reservation_guests.first_name, guests.first_name, ""), " ", COALESCE(reservation_guests.last_name, guests.last_name, ""))) as guest_name,
                reservations.reservation_number,
                pos_orders.room_id,
                pos_outlets.name as outlet_name,
                COUNT(pos_orders.id) as total_orders,
                SUM(pos_orders.subtotal) as subtotal,
                SUM(pos_orders.tax_amount) as tax,
                SUM(pos_orders.discount_amount) as discount,
                SUM(pos_orders.grand_total) as grand_total
            ')
            ->groupBy(
                'pos_orders.guest_id',
                'reservation_guests.first_name',
                'reservation_guests.last_name',
                'guests.first_name',
                'guests.last_name',
                'reservations.reservation_number',
                'pos_orders.room_id',
                'pos_outlets.name'
            )
            ->orderBy('reservation_guests.first_name');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn ($r) => [
            $r->guest_name,
            $r->reservation_number,
            $r->room_id,
            $r->outlet_name,
            $r->total_orders,
            number_format($r->subtotal, 2),
            number_format($r->tax, 2),
            number_format($r->discount, 2),
            number_format($r->grand_total, 2),
        ]);
    }

    public function headings(): array
    {
        return ['Guest', 'Reservation #', 'Room', 'Outlet', 'Orders', 'Subtotal (Rs.)', 'Tax (Rs.)', 'Discount (Rs.)', 'Grand Total (Rs.)'];
    }

    public function title(): string
    {
        return 'Guest Billing';
    }
}
