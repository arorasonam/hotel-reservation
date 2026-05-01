<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrderTypeExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
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
        ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
        ->selectRaw('
            pos_outlets.name as outlet_name,
            pos_orders.order_type,
            COUNT(pos_orders.id) as total_orders,
            SUM(pos_orders.subtotal) as subtotal,
            SUM(pos_orders.tax_amount) as tax,
            SUM(pos_orders.discount_amount) as discount,
            SUM(pos_orders.grand_total) as revenue
        ')
        ->groupBy('pos_outlets.id', 'pos_outlets.name', 'pos_orders.order_type')
        ->orderBy('pos_outlets.name');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn($r) => [
            $r->outlet_name,
            str_replace('_', ' ', ucfirst($r->order_type)),
            $r->total_orders,
            number_format($r->subtotal, 2),
            number_format($r->tax, 2),
            number_format($r->discount, 2),
            number_format($r->revenue, 2),
        ]);
    }

    public function headings(): array
    {
        return ['Outlet', 'Order Type', 'Orders', 'Subtotal (₹)', 'Tax (₹)', 'Discount (₹)', 'Revenue (₹)'];
    }

    public function title(): string { return 'Order Type Breakdown'; }
}

