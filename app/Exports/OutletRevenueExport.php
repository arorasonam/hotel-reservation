<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OutletRevenueExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
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
            ->orderByDesc('revenue');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()
            ->map(fn ($r) => [
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

    public function title(): string
    {
        return 'Outlet Revenue';
    }
}
