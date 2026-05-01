<?php

namespace App\Filament\Pages\Reports;

use App\Exports\GstTaxExport;
use App\Models\PosOrderItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;

class GstTaxReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-receipt-percent';
    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';
    protected static ?string $navigationLabel = 'GST / Tax Report';
    protected static ?int    $navigationSort  = 6;
    protected string  $view            = 'filament.pages.reports.gst-tax';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date_from')->label('From')->default(now()->startOfMonth()),
            DatePicker::make('date_to')->label('To')->default(now()),
            Select::make('outlet_id')
                ->label('Outlet')
                ->placeholder('All Outlets')
                ->options(\App\Models\PosOutlet::where('status', 1)->pluck('name', 'id'))
                ->searchable(),
        ])->columns(3);
    }

    public function getStats(): array
    {
        [$from, $to] = $this->dateRange();

        $totals = $this->getBaseQuery()
            ->selectRaw('
                SUM(poi.subtotal) as taxable_amount,
                SUM(poi.tax_amount) as total_tax,
                SUM(poi.total) as gross_total,
                COUNT(DISTINCT poi.pos_order_id) as total_orders
            ')
            ->first();

        return [
            ['label' => 'Taxable Amount',  'value' => '₹' . number_format($totals->taxable_amount, 2)],
            ['label' => 'Total Tax',       'value' => '₹' . number_format($totals->total_tax, 2)],
            ['label' => 'Gross Total',     'value' => '₹' . number_format($totals->gross_total, 2)],
            ['label' => 'Orders',          'value' => number_format($totals->total_orders)],
        ];
    }

    public function getTableData(): \Illuminate\Support\Collection
    {
        // Group by tax slab (tax_percentage) and category
        // Shows: tax %, category name, taxable amount, tax collected
        return $this->getBaseQuery()
            ->join('pos_categories', 'poi.pos_category_id', '=', 'pos_categories.id')
            ->selectRaw('
                poi.tax_percentage,
                pos_categories.name as category_name,
                COUNT(DISTINCT poi.pos_order_id) as orders,
                SUM(poi.quantity) as qty_sold,
                SUM(poi.subtotal) as taxable_amount,
                SUM(poi.tax_amount) as tax_collected,
                SUM(poi.total) as gross_amount
            ')
            ->groupBy('poi.tax_percentage', 'pos_categories.id', 'pos_categories.name')
            ->orderBy('poi.tax_percentage')
            ->orderBy('pos_categories.name')
            ->get()
            ->map(function ($row) {
                // Split tax into CGST + SGST (half each) for GST compliance display
                $half = $row->tax_collected / 2;
                $row->cgst = $half;
                $row->sgst = $half;
                return $row;
            });
    }

    public function getTableColumns(): array
    {
        return ['Category', 'Tax %', 'Orders', 'Qty', 'Taxable Amount', 'CGST', 'SGST', 'Total Tax', 'Gross Amount'];
    }

    public function getExportClass(): string { return GstTaxExport::class; }

    private function getBaseQuery()
    {
        [$from, $to] = $this->dateRange();

        $query = PosOrderItem::query()
            ->from('pos_order_items as poi')
            ->join('pos_orders', 'poi.pos_order_id', '=', 'pos_orders.id')
            ->whereBetween('pos_orders.settled_at', [$from, $to])
            ->whereIn('pos_orders.status', ['paid', 'confirmed']);

        if ($this->outlet_id) {
            $query->where('pos_orders.pos_outlet_id', $this->outlet_id);
        }

        return $query;
    }
}