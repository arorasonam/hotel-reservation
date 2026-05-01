<?php
// app/Filament/Pages/Reports/CancelledOrdersReport.php

namespace App\Filament\Pages\Reports;

use App\Exports\CancelledOrdersExport;
use App\Models\PosOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;

class CancelledOrdersReport extends BaseReportPage
{
    protected static BackedEnum|string|null  $navigationIcon  = 'heroicon-o-x-circle';
    protected static UnitEnum|string|null  $navigationGroup = 'POS Reports';
    protected static ?string  $navigationLabel = 'Cancelled Orders';
    protected static ?int    $navigationSort  = 5;
    protected string  $view            = 'filament.pages.reports.cancelled-orders';

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

        // Compare cancelled vs total to show loss %
        $cancelled = $this->getBaseQuery()
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as revenue')
            ->first();

        $total = PosOrder::whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as revenue')
            ->first();

        $cancelRate = $total->orders > 0
            ? round(($cancelled->orders / $total->orders) * 100, 1)
            : 0;

        return [
            ['label' => 'Cancelled Orders',    'value' => number_format($cancelled->orders)],
            ['label' => 'Lost Revenue',         'value' => '₹' . number_format($cancelled->revenue, 2)],
            ['label' => 'Cancellation Rate',    'value' => $cancelRate . '%'],
            ['label' => 'Total Orders (period)','value' => number_format($total->orders)],
        ];
    }

    public function getTableData(): \Illuminate\Support\Collection
    {
        return $this->getBaseQuery()
            ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
            ->join('users', 'pos_orders.created_by', '=', 'users.id')
            ->select(
                'pos_orders.order_number',
                'pos_outlets.name as outlet_name',
                'pos_orders.order_type',
                'pos_orders.table_no',
                'pos_orders.subtotal',
                'pos_orders.tax_amount',
                'pos_orders.discount_amount',
                'pos_orders.grand_total',
                'users.name as created_by',
                'pos_orders.created_at',
                'pos_orders.updated_at as cancelled_at',
            )
            ->orderByDesc('pos_orders.updated_at')
            ->get();
    }

    public function getTableColumns(): array
    {
        return [
            'Order #', 'Outlet', 'Type', 'Table',
            'Subtotal', 'Tax', 'Discount', 'Grand Total',
            'Created By', 'Created At', 'Cancelled At',
        ];
    }

    public function getExportClass(): string { return CancelledOrdersExport::class; }

    private function getBaseQuery()
    {
        [$from, $to] = $this->dateRange();

        $query = PosOrder::from('pos_orders')
            ->whereBetween('pos_orders.updated_at', [$from, $to])
            ->where('pos_orders.status', 'cancelled');

        if ($this->outlet_id) {
            $query->where('pos_orders.pos_outlet_id', $this->outlet_id);
        }

        return $query;
    }
}