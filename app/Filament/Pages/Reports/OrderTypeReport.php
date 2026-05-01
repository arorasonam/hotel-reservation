<?php

namespace App\Filament\Pages\Reports;

use App\Exports\OrderTypeExport;
use App\Models\PosOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;

class OrderTypeReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-queue-list';
    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';
    protected static ?string $navigationLabel = 'Order Type Breakdown';
    protected static ?int    $navigationSort  = 4;
    protected string  $view  = 'filament.pages.reports.order-type';

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
        $data = $this->getTableData();

        return [
            ['label' => 'Room Charge Orders',  'value' => number_format($data->where('order_type', 'room_charge')->sum('total_orders'))],
            ['label' => 'Room Charge Revenue',  'value' => '₹' . number_format($data->where('order_type', 'room_charge')->sum('revenue'), 2)],
            ['label' => 'Walk-in Orders',       'value' => number_format($data->where('order_type', 'walk_in')->sum('total_orders'))],
            ['label' => 'Walk-in Revenue',      'value' => '₹' . number_format($data->where('order_type', 'walk_in')->sum('revenue'), 2)],
            ['label' => 'Takeaway Orders',      'value' => number_format($data->where('order_type', 'takeaway')->sum('total_orders'))],
            ['label' => 'Takeaway Revenue',     'value' => '₹' . number_format($data->where('order_type', 'takeaway')->sum('revenue'), 2)],
        ];
    }

    public function getTableData(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->dateRange();

        $query = PosOrder::whereBetween('settled_at', [$from, $to])
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
            ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
            ->selectRaw('
                pos_outlets.name as outlet_name,
                pos_orders.order_type,
                COUNT(pos_orders.id) as total_orders,
                SUM(pos_orders.subtotal) as subtotal,
                SUM(pos_orders.tax_amount) as tax,
                SUM(pos_orders.discount_amount) as discount,
                SUM(pos_orders.grand_total) as revenue
            ')
            ->groupBy('pos_outlets.id', 'pos_outlets.name', 'pos_orders.order_type')
            ->orderBy('pos_outlets.name')
            ->orderBy('pos_orders.order_type');

        if ($this->outlet_id) {
            $query->where('pos_orders.pos_outlet_id', $this->outlet_id);
        }

        return $query->get();
    }

    public function getTableColumns(): array
    {
        return ['Outlet', 'Order Type', 'Orders', 'Subtotal', 'Tax', 'Discount', 'Revenue'];
    }

    public function getExportClass(): string { return OrderTypeExport::class; }
}