<?php

namespace App\Exports;

use App\Models\PosOrder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailySalesExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
        ->selectRaw("
            DATE(settled_at) as date,
            COUNT(*) as total_orders,
            SUM(subtotal) as subtotal,
            SUM(tax_amount) as tax,
            SUM(discount_amount) as discount,
            SUM(grand_total) as revenue
        ")
        ->groupByRaw('DATE(settled_at)')
        ->orderByRaw('DATE(settled_at) DESC');

        if ($this->outletId) {
            $query->where('pos_outlet_id', $this->outletId);
        }

        return $query->get()->map(fn($r) => [
            Carbon::parse($r->date)->format('d M Y'),
            $r->total_orders,
            number_format($r->subtotal, 2),
            number_format($r->tax, 2),
            number_format($r->discount, 2),
            number_format($r->revenue, 2),
        ]);
    }

    public function headings(): array
    {
        return ['Date', 'Orders', 'Subtotal (₹)', 'Tax (₹)', 'Discount (₹)', 'Revenue (₹)'];
    }

    public function title(): string { return 'Daily Sales'; }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E2EFDA']]],
        ];
    }
}

