<?php

// app/Filament/Pages/Reports/DailySalesReport.php

namespace App\Filament\Pages\Reports;

use App\Exports\DailySalesExport;
use App\Models\PosOrder;
use App\Models\PosOutlet;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use UnitEnum;

class DailySalesReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';

    protected static ?string $navigationLabel = 'Daily Sales';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports.daily-sales';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date_from')->label('From')->default(now()->startOfMonth()),
            DatePicker::make('date_to')->label('To')->default(now()),
            Select::make('outlet_id')
                ->label('Outlet')
                ->placeholder('All Outlets')
                ->options(PosOutlet::where('status', 1)->pluck('name', 'id'))
                ->searchable(),
        ])->columns(3);
    }

    public function getStats(): array
    {
        $data = $this->getBaseQuery()
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(COALESCE(base_grand_total, grand_total)) as total_revenue,
                SUM(COALESCE(base_subtotal, subtotal)) as total_subtotal,
                SUM(COALESCE(base_tax_amount, tax_amount)) as total_tax,
                SUM(COALESCE(base_discount_amount, discount_amount)) as total_discount,
                AVG(COALESCE(base_grand_total, grand_total)) as avg_order_value
            ')
            ->first();

        return [
            ['label' => 'Total Orders',      'value' => number_format($data->total_orders)],
            ['label' => 'Total Revenue',      'value' => '₹'.number_format($data->total_revenue, 2)],
            ['label' => 'Subtotal',           'value' => '₹'.number_format($data->total_subtotal, 2)],
            ['label' => 'Tax Collected',      'value' => '₹'.number_format($data->total_tax, 2)],
            ['label' => 'Discounts Given',    'value' => '₹'.number_format($data->total_discount, 2)],
            ['label' => 'Avg Order Value',    'value' => '₹'.number_format($data->avg_order_value, 2)],
        ];
    }

    public function getTableData(): Collection
    {
        return $this->getBaseQuery()
            ->selectRaw('
                DATE(settled_at) as date,
                COUNT(*) as total_orders,
                SUM(COALESCE(base_grand_total, grand_total)) as revenue,
                SUM(COALESCE(base_subtotal, subtotal)) as subtotal,
                SUM(COALESCE(base_tax_amount, tax_amount)) as tax,
                SUM(COALESCE(base_discount_amount, discount_amount)) as discount
            ')
            ->groupByRaw('DATE(settled_at)')
            ->orderByRaw('DATE(settled_at) DESC')
            ->get();
    }

    public function getTableColumns(): array
    {
        return ['Date', 'Orders', 'Subtotal', 'Tax', 'Discount', 'Revenue'];
    }

    public function getExportClass(): string
    {
        return DailySalesExport::class;
    }

    private function getBaseQuery()
    {
        [$from, $to] = $this->dateRange();

        $query = PosOrder::whereBetween('settled_at', [$from, $to])
            ->whereIn('status', ['paid', 'confirmed']);

        if ($this->outlet_id) {
            $query->where('pos_outlet_id', $this->outlet_id);
        }

        return $query;
    }
}
