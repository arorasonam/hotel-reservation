<?php

namespace App\Filament\Pages;

use App\Models\Reservation;
use App\Models\ReservationRoomCategory;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class ReservationInsights extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static UnitEnum|string|null $navigationGroup = 'Reservation Management';

    protected static ?string $title = 'Reservation Insights';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reservation-insights';

    // ─── Tab state ───────────────────────────────────────────────
    public string $activeTab = 'revenue';

    // ─── Filter state ────────────────────────────────────────────
    public string $period = 'month';   // day | week | month

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function getHeading(): string
    {
        return '';
    }

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
    }

    // ─── Tab switcher ─────────────────────────────────────────────
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── Period switcher ──────────────────────────────────────────
    public function setPeriod(string $period): void
    {
        $this->period = $period;
        [$this->dateFrom, $this->dateTo] = match ($period) {
            'day' => [now()->toDateString(), now()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            default => [$this->dateFrom, $this->dateTo],
        };
    }

    // ═══════════════════════════════════════════════════════════════
    //  REVENUE TAB DATA
    // ═══════════════════════════════════════════════════════════════

    /**
     * Revenue trend grouped by date and booking_source.name
     * Returns: { labels: [...], datasets: [{label, data, borderColor}] }
     */
    public function getRevenueTrendData(): array
    {
        $rows = Reservation::query()
            ->leftJoin('booking_sources', 'reservations.booking_source_id', '=', 'booking_sources.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                DB::raw('DATE(reservations.check_in) as date'),
                DB::raw("COALESCE(booking_sources.name, 'Direct') as source"),
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            // FIX: Use the actual table column name here
            ->groupBy(DB::raw('DATE(reservations.check_in)'), DB::raw("COALESCE(booking_sources.name, 'Direct')"))
            ->orderBy('date')
            ->get();

        // Build a sorted unique list of dates
        $dates = $rows->pluck('date')->unique()->sort()->values();
        $sources = $rows->pluck('source')->unique()->values();

        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#9b7b62', '#9e9e9e', '#2b9bea', '#625bd3', '#f45b83', '#d95cec', '#20d983'];

        $datasets = $sources->map(function ($source, $i) use ($rows, $dates, $colors) {
            $dataMap = $rows->where('source', $source)->pluck('revenue', 'date');

            return [
                'label' => $source,
                'data' => $dates->map(fn ($d) => round((float) ($dataMap[$d] ?? 0), 2))->values()->all(),
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => $colors[$i % count($colors)],
                'tension' => 0.18,
                'fill' => false,
            ];
        })->values()->all();

        return [
            'labels' => $dates->values()->all(),
            'datasets' => $datasets,
        ];
    }

    /**
     * Channel mix (doughnut) – revenue share by booking source
     */
    public function getChannelMixData(): array
    {
        $rows = Reservation::query()
            ->leftJoin('booking_sources', 'reservations.booking_source_id', '=', 'booking_sources.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                DB::raw("COALESCE(booking_sources.name, 'Direct') as source"),
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            // FIX: Group by the actual column, not the alias "source"
            ->groupBy(DB::raw("COALESCE(booking_sources.name, 'Direct')"))
            ->orderByDesc('revenue')
            ->get();

        $total = $rows->sum('revenue') ?: 1;
        $colors = ['#37c8e9', '#12a8d8', '#ff7f00', '#ffb43b', '#39e80b', '#f45b83', '#e0d647', '#ffe85a', '#9b7b62', '#9e9e9e'];

        return [
            'labels' => $rows->pluck('source')->values()->all(),
            'datasets' => [[
                'label' => 'Revenue Share',
                'data' => $rows->map(fn ($r) => round($r->revenue / $total * 100, 2))->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Weekdays revenue (bar) – sum per day-of-week by source
     */
    public function getWeekdaysRevenueData(): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#9b7b62', '#9e9e9e', '#2b9bea', '#625bd3', '#f45b83', '#d95cec', '#20d983'];

        $rows = Reservation::query()
            ->leftJoin('booking_sources', 'reservations.booking_source_id', '=', 'booking_sources.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                DB::raw('EXTRACT(DOW FROM reservations.check_in)::int as dow'),
                DB::raw("COALESCE(booking_sources.name, 'Direct') as source"),
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            // FIX: Use the actual table column and raw expression here
            ->groupBy(DB::raw('EXTRACT(DOW FROM reservations.check_in)::int'), DB::raw("COALESCE(booking_sources.name, 'Direct')"))
            ->get();

        $sources = $rows->pluck('source')->unique()->values();

        $datasets = $sources->map(function ($source, $i) use ($rows, $colors) {
            $byDow = $rows->where('source', $source)->pluck('revenue', 'dow');

            return [
                'label' => $source,
                'data' => collect(range(0, 6))->map(fn ($d) => round((float) ($byDow[$d] ?? 0), 2))->values()->all(),
                'backgroundColor' => $colors[$i % count($colors)],
            ];
        })->values()->all();

        return ['labels' => $days, 'datasets' => $datasets];
    }

    /**
     * Revenue split (pie) – Room vs Add-on vs Service Shop
     * Adjust column names to match your actual schema.
     */
    public function getRevenueSplitData(): array
    {
        // If you have separate columns for revenue types, adjust here.
        // Example assumes total_amount is room revenue and you may have
        // addon_amount / service_amount columns.
        $row = Reservation::query()
            ->whereBetween('check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->selectRaw('
            SUM(total_amount) as room_revenue,
            0 as addon_revenue, 
            0 as service_revenue
        ')
            ->first();

        $room = (float) ($row->room_revenue ?? 0);
        $addon = (float) ($row->addon_revenue ?? 0);
        $service = (float) ($row->service_revenue ?? 0);

        return [
            'labels' => ['Room Revenue', 'Add-On Revenue', 'Service Shop Revenue'],
            'datasets' => [[
                'label' => 'Revenue Split',
                'data' => [$room, $addon, $service],
                'backgroundColor' => ['#12a8d8', '#ff7f00', '#39e80b'],
            ]],
        ];
    }

    /**
     * Revenue by rate plan (pie)
     */
    public function getRevenueByRatePlanData(): array
    {
        $tableName = 'meal_plans';

        $rows = ReservationRoomCategory::query()
            ->join('reservations', 'reservation_room_categories.reservation_id', '=', 'reservations.id')
            ->join($tableName, 'reservation_room_categories.meal_plan_id', '=', "$tableName.id")
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                "$tableName.name as plan",
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            /**
             * Postgres Fix: You must group by the literal table column name.
             * Using the alias "plan" here will trigger a SQLSTATE[42803] error.
             */
            ->groupBy("$tableName.name")
            ->orderByDesc('revenue')
            ->get();
        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#f45b83', '#8b5cf6'];

        return [
            'labels' => $rows->pluck('plan')->values()->all(),
            'datasets' => [[
                'label' => 'Rate Plan Revenue',
                'data' => $rows->map(fn ($r) => round((float) $r->revenue, 2))->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Category mix – revenue by room type
     */
    public function getCategoryMixRevenueData(): array
    {
        $rows = Reservation::query()
            ->join('reservation_room_categories as rc', 'reservations.id', '=', 'rc.reservation_id')
            ->join('room_types', 'rc.room_type_id', '=', 'room_types.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select('room_types.name as room_type', DB::raw('SUM(reservations.total_amount) as revenue'))
            ->groupBy('room_types.name')
            ->orderByDesc('revenue')
            ->get();

        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#f45b83', '#8b5cf6', '#ec4899'];

        return [
            'labels' => $rows->pluck('room_type')->values()->all(),
            'datasets' => [[
                'data' => $rows->map(fn ($r) => round((float) $r->revenue, 2))->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Meal plan mix – revenue by meal plan
     */
    public function getMealPlanMixData(): array
    {
        $rows = ReservationRoomCategory::query()
            // Join the parent reservation to get the check-in dates and status
            ->join('reservations', 'reservation_room_categories.reservation_id', '=', 'reservations.id')
            // Join the meal plans table to get the names
            ->join('meal_plans', 'reservation_room_categories.meal_plan_id', '=', 'meal_plans.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                'meal_plans.name as plan',
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            // Postgres Fix: Group by the literal column name to avoid SQLSTATE[42803]
            ->groupBy('meal_plans.name')
            ->orderByDesc('revenue')
            ->get();

        $colors = ['#39e80b', '#ff7f00', '#12a8d8', '#f45b83', '#8b5cf6'];

        return [
            'labels' => $rows->pluck('plan')->values()->all(),
            'datasets' => [[
                'data' => $rows->map(fn ($r) => round((float) $r->revenue, 2))->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Market segmentation – revenue by source_market
     */
    public function getMarketSegmentationRevenueData(): array
    {
        $rows = Reservation::query()
            ->join('source_markets', 'reservations.source_market_id', '=', 'source_markets.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select('source_markets.name as market', DB::raw('SUM(reservations.total_amount) as revenue'))
            ->groupBy('source_markets.name')
            ->orderByDesc('revenue')
            ->get();

        $colors = ['#39e80b', '#12a8d8', '#ff7f00', '#f45b83', '#8b5cf6'];

        return [
            'labels' => $rows->pluck('market')->values()->all(),
            'datasets' => [[
                'data' => $rows->map(fn ($r) => round((float) $r->revenue, 2))->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  OCCUPANCY TAB DATA
    // ═══════════════════════════════════════════════════════════════

    /**
     * Occupancy trend – % per date (confirmed rooms / total rooms)
     */
    public function getOccupancyTrendData(): array
    {
        // Total rooms per hotel – adjust if you have a `hotel_rooms` or `rooms` table
        $totalRooms = DB::table('hotel_rooms')->count() ?: 1;

        $rows = Reservation::query()
            ->leftJoin('booking_sources', 'reservations.booking_source_id', '=', 'booking_sources.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                DB::raw('DATE(reservations.check_in) as date'),
                DB::raw("COALESCE(booking_sources.name, 'Direct') as source"),
                DB::raw('COUNT(*) as nights')
            )
            ->groupBy(DB::raw('DATE(reservations.check_in)'), DB::raw("COALESCE(booking_sources.name, 'Direct')"))
            ->orderBy('date')
            ->get();

        $dates = $rows->pluck('date')->unique()->sort()->values();
        $sources = $rows->pluck('source')->unique()->values();
        $colors = ['#ff7f00', '#39e80b', '#9b7b62', '#9e9e9e', '#2b9bea', '#625bd3', '#f45b83', '#d95cec', '#20d983'];

        // TOTAL dataset
        $totals = $rows->groupBy('date')->map(fn ($g) => round($g->sum('nights') / $totalRooms * 100, 2));

        $datasets = [];
        $datasets[] = [
            'label' => 'TOTAL',
            'data' => $dates->map(fn ($d) => $totals[$d] ?? 0)->values()->all(),
            'borderColor' => '#12a8d8',
            'backgroundColor' => '#12a8d8',
            'tension' => 0.18,
            'fill' => false,
        ];

        foreach ($sources as $i => $source) {
            $byDate = $rows->where('source', $source)->pluck('nights', 'date');
            $datasets[] = [
                'label' => $source,
                'data' => $dates->map(fn ($d) => round((float) ($byDate[$d] ?? 0) / $totalRooms * 100, 2))->values()->all(),
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => $colors[$i % count($colors)],
                'tension' => 0.18,
                'fill' => false,
            ];
        }

        return ['labels' => $dates->values()->all(), 'datasets' => $datasets];
    }

    /**
     * Channel mix for occupancy (doughnut) – rooms by booking source
     */
    public function getChannelMixOccupancyData(): array
    {
        $rows = Reservation::query()
            ->leftJoin('booking_sources', 'reservations.booking_source_id', '=', 'booking_sources.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(DB::raw("COALESCE(booking_sources.name, 'Direct') as source"), DB::raw('COUNT(*) as cnt'))
            ->groupBy(DB::raw("COALESCE(booking_sources.name, 'Direct')"))
            ->orderByDesc('cnt')
            ->get();

        $total = $rows->sum('cnt') ?: 1;
        $colors = ['#37c8e9', '#12a8d8', '#ff7f00', '#39e80b', '#f45b83', '#e0d647', '#9b7b62'];

        return [
            'labels' => $rows->pluck('source')->values()->all(),
            'datasets' => [[
                'label' => 'Room Share',
                'data' => $rows->map(fn ($r) => round($r->cnt / $total * 100, 2))->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Weekdays occupancy (stacked bar)
     */
    public function getWeekdaysOccupancyData(): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#9b7b62', '#9e9e9e', '#2b9bea', '#625bd3', '#f45b83'];
        $totalRooms = DB::table('hotel_rooms')->count() ?: 1;

        $rows = Reservation::query()
            ->leftJoin('booking_sources', 'reservations.booking_source_id', '=', 'booking_sources.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select(
                DB::raw('EXTRACT(DOW FROM reservations.check_in)::int as dow'),
                DB::raw("COALESCE(booking_sources.name, 'Direct') as source"),
                DB::raw('COUNT(*) as cnt')
            )
            ->groupBy(DB::raw('EXTRACT(DOW FROM reservations.check_in)::int'), DB::raw("COALESCE(booking_sources.name, 'Direct')"))
            ->get();

        $sources = $rows->pluck('source')->unique()->values();

        $datasets = $sources->map(function ($source, $i) use ($rows, $colors, $totalRooms) {
            $byDow = $rows->where('source', $source)->pluck('cnt', 'dow');

            return [
                'label' => $source,
                'data' => collect(range(0, 6))->map(fn ($d) => round((float) ($byDow[$d] ?? 0) / $totalRooms * 100, 2))->values()->all(),
                'backgroundColor' => $colors[$i % count($colors)],
            ];
        })->values()->all();

        return ['labels' => $days, 'datasets' => $datasets];
    }

    /**
     * Category mix for occupancy (pie) – rooms by room type
     */
    public function getCategoryMixOccupancyData(): array
    {
        $rows = Reservation::query()
            ->join('reservation_room_categories as rc', 'reservations.id', '=', 'rc.reservation_id')
            ->join('room_types', 'rc.room_type_id', '=', 'room_types.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select('room_types.name as room_type', DB::raw('SUM(rc.rooms_count) as cnt'))
            ->groupBy('room_types.name')
            ->orderByDesc('cnt')
            ->get();

        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#f45b83', '#8b5cf6'];

        return [
            'labels' => $rows->pluck('room_type')->values()->all(),
            'datasets' => [[
                'data' => $rows->pluck('cnt')->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Meal plan mix for occupancy – rooms by meal plan
     */
    public function getMealPlanMixOccupancyData(): array
    {
        $rows = ReservationRoomCategory::query()
            ->join('reservations', 'reservation_room_categories.reservation_id', '=', 'reservations.id')
            ->join('meal_plans', 'reservation_room_categories.meal_plan_id', '=', 'meal_plans.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select('meal_plans.name as plan', DB::raw('SUM(reservation_room_categories.rooms_count) as cnt'))
            ->groupBy('meal_plans.name')
            ->orderByDesc('cnt')
            ->get();

        $colors = ['#12a8d8', '#ff7f00', '#39e80b', '#ef4444'];

        return [
            'labels' => $rows->pluck('plan')->values()->all(),
            'datasets' => [[
                'data' => $rows->pluck('cnt')->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    /**
     * Market segmentation for occupancy
     */
    public function getMarketSegmentationOccupancyData(): array
    {
        $rows = Reservation::query()
            ->join('source_markets', 'reservations.source_market_id', '=', 'source_markets.id')
            ->whereBetween('reservations.check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('reservations.status', ['cancelled', 'no_show'])
            ->select('source_markets.name as market', DB::raw('COUNT(*) as cnt'))
            ->groupBy('source_markets.name')
            ->orderByDesc('cnt')
            ->get();

        $colors = ['#39e80b', '#12a8d8', '#ff7f00', '#ef4444', '#8b5cf6'];

        return [
            'labels' => $rows->pluck('market')->values()->all(),
            'datasets' => [[
                'data' => $rows->pluck('cnt')->values()->all(),
                'backgroundColor' => collect($colors)->take($rows->count())->values()->all(),
            ]],
        ];
    }

    // ─── Summary KPIs ─────────────────────────────────────────────

    public function getSummaryStats(): array
    {
        $totalRooms = DB::table('hotel_rooms')->count() ?: 1;

        $base = Reservation::query()
            ->whereBetween('check_in', [$this->dateFrom, $this->dateTo])
            ->whereNotIn('status', ['cancelled', 'no_show']);

        $revenue = (float) $base->clone()->sum('total_amount');
        $roomNights = $base->clone()->count();
        $occupancy = round($roomNights / $totalRooms * 100, 2);
        $arr = $roomNights > 0 ? round($revenue / $roomNights, 2) : 0;
        $preDays = $base->clone()
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (created_at::timestamp - check_in::timestamp)) / 86400) as avg_days')
            ->value('avg_days');

        return [
            'revenue' => number_format($revenue, 2),
            'occupancy' => $occupancy.'%',
            'arr' => number_format($arr, 2),
            'pre_booking' => round(abs($preDays ?? 0), 2).' days',
        ];
    }
}
