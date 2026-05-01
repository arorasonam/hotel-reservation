<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OutletRevenueExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string  $dateFrom,
        private string  $dateTo,
        private ?string $outletId = null,
    ) {}

    public function collection()
    {
        return PosOrder::whereBetween('settled_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        ])
        ->whereIn('status', ['paid', 'confirmed'])
        ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
        ->selectRaw('
            pos_outlets.name as outlet_name,
            COUNT(pos_orders.id) as total_orders,
            SUM(pos_orders.subtotal) as subtotal,
            SUM(pos_orders.tax_amount) as tax,
            SUM(pos_orders.discount_amount) as discount,
            SUM(pos_orders.grand_total) as revenue
        ')
        ->groupBy('pos_outlets.id', 'pos_outlets.name')
        ->orderByDesc('revenue')
        ->get()
        ->map(fn($r) => [
            $r->outlet_name,
            $r->total_orders,
            number_format($r->subtotal, 2),
            number_format($r->tax, 2),
            number_format($r->discount, 2),
            number_format($r->revenue, 2),
        ]);
    }

    public function headings(): array
    {
        return ['Outlet', 'Orders', 'Subtotal (₹)', 'Tax (₹)', 'Discount (₹)', 'Revenue (₹)'];
    }

    public function title(): string { return 'Outlet Revenue'; }
}
