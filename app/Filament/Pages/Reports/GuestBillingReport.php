<?php

namespace App\Filament\Pages\Reports;

use App\Exports\GuestBillingExport;
use App\Models\PosOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;

class GuestBillingReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-user-circle';
    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';
    protected static ?string $navigationLabel = 'Guest Billing';
    protected static ?int    $navigationSort  = 7;
    protected string  $view            = 'filament.pages.reports.guest-billing';

    public function Schema(Form $schema): Schema
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
                COUNT(*) as total_orders,
                COUNT(DISTINCT guest_id) as unique_guests,
                SUM(grand_total) as total_charged,
                SUM(tax_amount) as total_tax
            ')
            ->first();

        return [
            ['label' => 'Room Charge Orders', 'value' => number_format($totals->total_orders)],
            ['label' => 'Unique Guests',       'value' => number_format($totals->unique_guests)],
            ['label' => 'Total Charged',       'value' => '₹' . number_format($totals->total_charged, 2)],
            ['label' => 'Tax on Room Charges', 'value' => '₹' . number_format($totals->total_tax, 2)],
        ];
    }

    public function getTableData(): \Illuminate\Support\Collection
    {
        // One row per guest per reservation — summed across all their POS orders
        return $this->getBaseQuery()
            ->join('reservation_guests', 'pos_orders.guest_id', '=', 'reservation_guests.id')
            ->join('reservations', 'pos_orders.reservation_id', '=', 'reservations.id')
            ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
            ->selectRaw('
                reservation_guests.id as guest_id,
                reservation_guests.first_name as guest_name,
                reservations.reservation_number,
                pos_orders.room_id,
                pos_outlets.name as outlet_name,
                COUNT(pos_orders.id) as total_orders,
                SUM(pos_orders.subtotal) as subtotal,
                SUM(pos_orders.tax_amount) as tax,
                SUM(pos_orders.discount_amount) as discount,
                SUM(pos_orders.grand_total) as grand_total,
                MIN(pos_orders.settled_at) as first_order,
                MAX(pos_orders.settled_at) as last_order
            ')
            ->groupBy(
                'reservation_guests.id', 'reservation_guests.first_name',
                'reservations.reservation_number',
                'pos_orders.room_id',
                'pos_outlets.name'
            )
            ->orderBy('reservation_guests.first_name')
            ->get();
    }

    public function getTableColumns(): array
    {
        return [
            'Guest', 'Reservation #', 'Room', 'Outlet',
            'Orders', 'Subtotal', 'Tax', 'Discount', 'Grand Total',
            'First Order', 'Last Order',
        ];
    }

    public function getExportClass(): string { return GuestBillingExport::class; }

    private function getBaseQuery()
    {
        [$from, $to] = $this->dateRange();

        $query = PosOrder::whereBetween('settled_at', [$from, $to])
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
            ->where('order_type', 'room_charge'); // only room charges

        if ($this->outlet_id) {
            $query->where('pos_outlet_id', $this->outlet_id);
        }

        return $query;
    }
}