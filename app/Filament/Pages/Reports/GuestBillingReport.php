<?php

namespace App\Filament\Pages\Reports;

use App\Exports\GuestBillingExport;
use App\Models\PosOrder;
use App\Models\PosOutlet;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use UnitEnum;

class GuestBillingReport extends BaseReportPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';

    protected static UnitEnum|string|null $navigationGroup = 'POS Reports';

    protected static ?string $navigationLabel = 'Guest Billing';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.reports.guest-billing';

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
        $totals = $this->getBaseQuery()
            ->selectRaw('
                COUNT(*) as total_orders,
                COUNT(DISTINCT guest_id) as unique_guests,
                SUM(COALESCE(base_grand_total, grand_total)) as total_charged,
                SUM(COALESCE(base_tax_amount, tax_amount)) as total_tax
            ')
            ->first();

        return [
            ['label' => 'Room Charge Orders', 'value' => number_format($totals->total_orders)],
            ['label' => 'Unique Guests',       'value' => number_format($totals->unique_guests)],
            ['label' => 'Total Charged',       'value' => 'Rs. '.number_format($totals->total_charged, 2)],
            ['label' => 'Tax on Room Charges', 'value' => 'Rs. '.number_format($totals->total_tax, 2)],
        ];
    }

    public function getTableData(): Collection
    {
        return $this->getBaseQuery()
            ->leftJoin('reservation_guests', function (JoinClause $join): void {
                $join->on('pos_orders.guest_id', '=', 'reservation_guests.guest_id')
                    ->on('pos_orders.reservation_id', '=', 'reservation_guests.reservation_id');
            })
            ->leftJoin('guests', 'pos_orders.guest_id', '=', 'guests.id')
            ->join('reservations', 'pos_orders.reservation_id', '=', 'reservations.id')
            ->join('pos_outlets', 'pos_orders.pos_outlet_id', '=', 'pos_outlets.id')
            ->selectRaw('
                pos_orders.guest_id,
                TRIM(CONCAT(COALESCE(reservation_guests.first_name, guests.first_name, ""), " ", COALESCE(reservation_guests.last_name, guests.last_name, ""))) as guest_name,
                reservations.reservation_number,
                pos_orders.room_id,
                pos_outlets.name as outlet_name,
                COUNT(pos_orders.id) as total_orders,
                SUM(COALESCE(pos_orders.base_subtotal, pos_orders.subtotal)) as subtotal,
                SUM(COALESCE(pos_orders.base_tax_amount, pos_orders.tax_amount)) as tax,
                SUM(COALESCE(pos_orders.base_discount_amount, pos_orders.discount_amount)) as discount,
                SUM(COALESCE(pos_orders.base_grand_total, pos_orders.grand_total)) as grand_total,
                MIN(COALESCE(pos_orders.settled_at, pos_orders.created_at)) as first_order,
                MAX(COALESCE(pos_orders.settled_at, pos_orders.created_at)) as last_order
            ')
            ->groupBy(
                'pos_orders.guest_id',
                'reservation_guests.first_name',
                'reservation_guests.last_name',
                'guests.first_name',
                'guests.last_name',
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

    public function getExportClass(): string
    {
        return GuestBillingExport::class;
    }

    private function getBaseQuery()
    {
        [$from, $to] = $this->dateRange();

        $query = PosOrder::where(function ($query) use ($from, $to): void {
            $query->whereBetween('pos_orders.settled_at', [$from, $to])
                ->orWhere(function ($query) use ($from, $to): void {
                    $query->whereNull('pos_orders.settled_at')
                        ->whereBetween('pos_orders.created_at', [$from, $to]);
                });
        })
            ->whereIn('pos_orders.status', ['paid', 'confirmed'])
            ->where('pos_orders.order_type', 'room_charge');

        if ($this->outlet_id) {
            $query->where('pos_orders.pos_outlet_id', $this->outlet_id);
        }

        return $query;
    }
}
