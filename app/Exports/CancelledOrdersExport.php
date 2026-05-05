<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CancelledOrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        private string $dateFrom,
        private string $dateTo,
        private ?string $outletId = null,
    ) {}

    public function collection()
    {
        $query = PosOrder::from('pos_orders')
            ->whereBetween('pos_orders.updated_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->where('pos_orders.status', 'cancelled')
            ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
            ->join('users', 'pos_orders.created_by', '=', 'users.id')
            ->select(
                'pos_orders.order_number',
                'pos_outlets.name as outlet_name',
                'pos_orders.order_type',
                'pos_orders.table_no',
                DB::raw('COALESCE(pos_orders.base_subtotal, pos_orders.subtotal) as subtotal'),
                DB::raw('COALESCE(pos_orders.base_tax_amount, pos_orders.tax_amount) as tax_amount'),
                DB::raw('COALESCE(pos_orders.base_discount_amount, pos_orders.discount_amount) as discount_amount'),
                DB::raw('COALESCE(pos_orders.base_grand_total, pos_orders.grand_total) as grand_total'),
                'users.name as created_by',
                'pos_orders.created_at',
                'pos_orders.updated_at as cancelled_at',
            )
            ->orderByDesc('pos_orders.updated_at');

        if ($this->outletId) {
            $query->where('pos_orders.pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn ($r) => [
            $r->order_number,
            $r->outlet_name,
            str_replace('_', ' ', ucfirst($r->order_type)),
            $r->table_no ?? '—',
            number_format($r->subtotal, 2),
            number_format($r->tax_amount, 2),
            number_format($r->discount_amount, 2),
            number_format($r->grand_total, 2),
            $r->created_by,
            Carbon::parse($r->created_at)->format('d M Y h:i A'),
            Carbon::parse($r->cancelled_at)->format('d M Y h:i A'),
        ]);
    }

    public function headings(): array
    {
        return ['Order #', 'Outlet', 'Type', 'Table', 'Subtotal (₹)', 'Tax (₹)', 'Discount (₹)', 'Grand Total (₹)', 'Created By', 'Created At', 'Cancelled At'];
    }

    public function title(): string
    {
        return 'Cancelled Orders';
    }
}
