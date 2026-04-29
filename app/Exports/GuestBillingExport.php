<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GuestBillingExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string  $dateFrom,
        private string  $dateTo,
        private ?string $outletId = null,
    ) {}

    public function collection()
    {
        $query = PosOrder::whereBetween('settled_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        ])
        ->whereIn('status', ['paid', 'confirmed'])
        ->where('order_type', 'room_charge')
        ->join('reservation_guests', 'pos_orders.guest_id', '=', 'reservation_guests.id')
        ->join('reservations', 'pos_orders.reservation_id', '=', 'reservations.id')
        ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
        ->selectRaw('
            reservation_guests.name as guest_name,
            reservations.reservation_number,
            pos_orders.room_id,
            pos_outlets.name as outlet_name,
            COUNT(pos_orders.id) as total_orders,
            SUM(pos_orders.subtotal) as subtotal,
            SUM(pos_orders.tax_amount) as tax,
            SUM(pos_orders.discount_amount) as discount,
            SUM(pos_orders.grand_total) as grand_total
        ')
        ->groupBy('reservation_guests.id', 'reservation_guests.name', 'reservations.reservation_number', 'pos_orders.room_id', 'pos_outlets.name')
        ->orderBy('reservation_guests.name');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn($r) => [
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
        return ['Guest', 'Reservation #', 'Room', 'Outlet', 'Orders', 'Subtotal (₹)', 'Tax (₹)', 'Discount (₹)', 'Grand Total (₹)'];
    }

    public function title(): string { return 'Guest Billing'; }
}

