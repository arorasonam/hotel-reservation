<?php

namespace App\Exports;

use App\Models\PosOrderItem;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CategoryItemExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
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
            ->join('pos_items', 'poi.pos_item_id', '=', 'pos_items.id')
            ->join('pos_categories', 'poi.pos_category_id', '=', 'pos_categories.id')
            ->whereBetween('pos_orders.settled_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
            ->selectRaw('
                pos_categories.name as category_name,
                pos_items.name as item_name,
                SUM(poi.quantity) as qty_sold,
                AVG(poi.price) as avg_price,
                SUM(poi.subtotal) as subtotal,
                SUM(poi.tax_amount) as tax,
                SUM(poi.total) as revenue
            ')
            ->groupBy('pos_items.id', 'pos_items.name', 'pos_categories.id', 'pos_categories.name')
            ->orderByDesc('revenue');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn($r) => [
            $r->category_name,
            $r->item_name,
            $r->qty_sold,
            number_format($r->avg_price, 2),
            number_format($r->subtotal, 2),
            number_format($r->tax, 2),
            number_format($r->revenue, 2),
        ]);
    }

    public function headings(): array
    {
        return ['Category', 'Item', 'Qty Sold', 'Avg Price (₹)', 'Subtotal (₹)', 'Tax (₹)', 'Revenue (₹)'];
    }

    public function title(): string { return 'Sales by Item'; }
}
