<?php
// ─────────────────────────────────────────────────────────────
// app/Filament/Pages/Reports/OutletRevenueReport.php
// ─────────────────────────────────────────────────────────────
namespace App\Filament\Pages\Reports;

use App\Exports\OutletRevenueExport;
use App\Models\PosOrder;
use Filament\Forms\Components\DatePicker;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;

class OutletRevenueReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-building-storefront';
    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';
    protected static ?string $navigationLabel = 'Revenue by Outlet';
    protected static ?int    $navigationSort  = 2;
    protected string  $view            = 'filament.pages.reports.outlet-revenue';

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date_from')->label('From')->default(now()->startOfMonth()),
            DatePicker::make('date_to')->label('To')->default(now()),
        ])->columns(2);
    }

    public function getStats(): array
    {
        [$from, $to] = $this->dateRange();

        $totals = PosOrder::whereBetween('settled_at', [$from, $to])
            ->whereIn('status', ['paid', 'confirmed'])
            ->selectRaw('SUM(grand_total) as revenue, COUNT(*) as orders')
            ->first();

        $topOutlet = $this->getTableData()->sortByDesc('revenue')->first();

        return [
            ['label' => 'Total Revenue',  'value' => '₹' . number_format($totals->revenue, 2)],
            ['label' => 'Total Orders',   'value' => number_format($totals->orders)],
            ['label' => 'Best Outlet',    'value' => $topOutlet?->outlet_name ?? '—'],
            ['label' => 'Outlets Active', 'value' => $this->getTableData()->count()],
        ];
    }

    public function getTableData(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->dateRange();

        return PosOrder::whereBetween('settled_at', [$from, $to])
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
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
            ->get();
    }

    public function getTableColumns(): array
    {
        return ['Outlet', 'Orders', 'Subtotal', 'Tax', 'Discount', 'Revenue'];
    }

    public function getExportClass(): string { return OutletRevenueExport::class; }
}