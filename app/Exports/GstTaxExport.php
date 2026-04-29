<?php

namespace App\Exports;

use App\Models\PosOrderItem;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GstTaxExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string  $dateFrom,
        private string  $dateTo,
        private ?string $outletId = null,
    ) {}

    public function collection()
    {
        $query = PosOrderItem::query()
            ->from('pos_order_items as poi')
            ->join('pos_orders', 'poi.pos_order_id', '=', 'pos_orders.id')
            ->join('pos_categories', 'poi.pos_category_id', '=', 'pos_categories.id')
            ->whereBetween('pos_orders.settled_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
            ->selectRaw('
                pos_categories.name as category_name,
                poi.tax_percentage,
                COUNT(DISTINCT poi.pos_order_id) as orders,
                SUM(poi.quantity) as qty_sold,
                SUM(poi.subtotal) as taxable_amount,
                SUM(poi.tax_amount) / 2 as cgst,
                SUM(poi.tax_amount) / 2 as sgst,
                SUM(poi.tax_amount) as total_tax,
                SUM(poi.total) as gross_amount
            ')
            ->groupBy('poi.tax_percentage', 'pos_categories.id', 'pos_categories.name')
            ->orderBy('poi.tax_percentage');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn($r) => [
            $r->category_name,
            $r->tax_percentage . '%',
            $r->orders,
            $r->qty_sold,
            number_format($r->taxable_amount, 2),
            number_format($r->cgst, 2),
            number_format($r->sgst, 2),
            number_format($r->total_tax, 2),
            number_format($r->gross_amount, 2),
        ]);
    }

    public function headings(): array
    {
        return ['Category', 'Tax %', 'Orders', 'Qty', 'Taxable Amount (₹)', 'CGST (₹)', 'SGST (₹)', 'Total Tax (₹)', 'Gross Amount (₹)'];
    }

    public function title(): string { return 'GST Tax Report'; }
}