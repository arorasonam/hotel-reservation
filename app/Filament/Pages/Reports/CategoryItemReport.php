<?php

namespace App\Filament\Pages\Reports;

use App\Exports\CategoryItemExport;
use App\Models\PosOrderItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;

class CategoryItemReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-chart-bar';
    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';
    protected static ?string $navigationLabel = 'Sales by Item';
    protected static ?int    $navigationSort  = 3;
    protected string  $view            = 'filament.pages.reports.category-item';

    public ?string $group_by = 'item'; // 'category' or 'item'

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
            Select::make('group_by')
                ->label('Group By')
                ->options(['category' => 'Category', 'item' => 'Item'])
                ->default('item'),
        ])->columns(4);
    }

    public function getStats(): array
    {
        $data = $this->getBaseQuery()
            ->selectRaw('
                SUM(poi.quantity) as total_qty,
                SUM(poi.total) as total_revenue,
                COUNT(DISTINCT poi.pos_order_id) as total_orders,
                COUNT(DISTINCT poi.pos_item_id) as unique_items
            ')
            ->first();

        return [
            ['label' => 'Total Qty Sold',  'value' => number_format($data->total_qty)],
            ['label' => 'Total Revenue',   'value' => '₹' . number_format($data->total_revenue, 2)],
            ['label' => 'Orders',          'value' => number_format($data->total_orders)],
            ['label' => 'Unique Items',    'value' => number_format($data->unique_items)],
        ];
    }

    public function getTableData(): \Illuminate\Support\Collection
    {
        if ($this->group_by === 'category') {
            return $this->getBaseQuery()
                ->join('pos_categories', 'poi.pos_category_id', '=', 'pos_categories.id')
                ->selectRaw('
                    pos_categories.name as category_name,
                    SUM(poi.quantity) as qty_sold,
                    SUM(poi.subtotal) as subtotal,
                    SUM(poi.tax_amount) as tax,
                    SUM(poi.total) as revenue
                ')
                ->groupBy('pos_categories.id', 'pos_categories.name')
                ->orderByDesc('revenue')
                ->get();
        }

        // Group by item (default)
        return $this->getBaseQuery()
            ->join('pos_items', 'poi.pos_item_id', '=', 'pos_items.id')
            ->join('pos_categories', 'poi.pos_category_id', '=', 'pos_categories.id')
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
            ->orderByDesc('revenue')
            ->get();
    }

    public function getTableColumns(): array
    {
        return $this->group_by === 'category'
            ? ['Category', 'Qty Sold', 'Subtotal', 'Tax', 'Revenue']
            : ['Category', 'Item', 'Qty Sold', 'Avg Price', 'Subtotal', 'Tax', 'Revenue'];
    }

    public function getExportClass(): string { return CategoryItemExport::class; }

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
