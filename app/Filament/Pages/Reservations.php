<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\HotelRoom;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use BackedEnum;

class Reservations extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Reservations';
    protected string $view = 'filament.pages.reservations-calendar';
    protected static ?string $slug = 'hotel-calendar';

    /* ─────────────────────────────────────────────────────────────
       VIEW DATA
    ───────────────────────────────────────────────────────────── */
    public function getViewData(): array
    {
        $groupedRooms = RoomType::with('rooms')->get()->map(function ($type) {
            return [
                'code'       => $type->code,
                'label'      => $type->name,
                'totalRooms' => $type->rooms->count(),
                'rooms'      => $type->rooms->map(fn($room) => [
                    'room_number' => $room->room_number,
                    'status'      => $this->safeCol($room, 'status', 'clean'),
                ])->toArray(),
            ];
        });

        $reservations = $this->loadReservations();
        $totalVacant = $this->countVacantRooms();

        return [
            'hotels'       => $this->getHotels(),
            'roomTypes'    => $this->getRoomTypes(),
            'groupedRooms' => $groupedRooms,
            'totalVacant'  => $totalVacant,
            'reservations' => $reservations,
        ];
    }

    private function loadReservations(): array
    {
        return Reservation::with(['reservationGuests'])
            ->whereIn('status', ['confirmed', 'tentative', 'waitlist', 'checked_in'])
            ->get()
            ->map(function ($res) {
                // Get Primary Guest or fallback to first guest
                $primary = $res->reservationGuests->where('is_primary', true)->first()
                    ?? $res->reservationGuests->first();

                return [
                    'id'             => $res->id,
                    'reservation_id' => $res->reservation_number, // The GRA_0000001 format
                    'room_no'        => (string) $res->room_no,
                    'first_name'     => $primary?->first_name ?? 'Guest',
                    'last_name'      => $primary?->last_name ?? '',
                    'check_in'       => $res->check_in ? Carbon::parse($res->check_in)->format('Y-m-d') : null,
                    'check_out'      => $res->check_out ? Carbon::parse($res->check_out)->format('Y-m-d') : null,
                    'nights'         => $res->nights ?? 1,
                    'status'         => $res->status,
                    'booking_type'   => $this->mapBookingType($res->status),
                    'verified'       => in_array($res->status, ['confirmed', 'checked_in']),
                ];
            })->toArray();
    }

    private function countVacantRooms(): int
    {
        try {
            $cols = Schema::getColumnListing((new HotelRoom)->getTable());
            return in_array('status', $cols)
                ? HotelRoom::whereIn('status', ['clean', 'vacant'])->count()
                : HotelRoom::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /* ─────────────────────────────────────────────────────────────
       LIVEWIRE ACTION — updateRoomStatus
       Called from JS: await lwCall('updateRoomStatus', roomNo, status)
    ───────────────────────────────────────────────────────────── */
    public function updateRoomStatus(string $roomNo, string $status): array
    {
        $allowed = ['clean', 'dirty', 'mnt', 'ooo', 'complaint', 'sanitised', 'vip', 'inspect', 'discrepancy'];

        if (! in_array($status, $allowed)) {
            return ['success' => false, 'message' => 'Invalid status value: ' . $status];
        }

        $roomCols  = Schema::getColumnListing((new HotelRoom)->getTable());
        $roomNoCol = in_array('room_number', $roomCols) ? 'room_number'
            : (in_array('room_no', $roomCols)   ? 'room_no' : null);

        if (! $roomNoCol) {
            return ['success' => false, 'message' => 'Cannot find room_number column on rooms table'];
        }

        $room = HotelRoom::where($roomNoCol, $roomNo)->first();
        if (! $room) {
            return ['success' => false, 'message' => "Room {$roomNo} not found"];
        }

        if (! in_array('status', $roomCols)) {
            return [
                'success' => false,
                'message' => "Column 'status' missing from rooms table — run: php artisan migrate",
            ];
        }

        $room->update(['status' => $status]);

        return ['success' => true, 'message' => "Room {$roomNo} → {$status}"];
    }

    /* ─────────────────────────────────────────────────────────────
       LIVEWIRE ACTION — storeReservation
       Called from JS: await lwCall('storeReservation', payload)
    ───────────────────────────────────────────────────────────── */
    public function storeReservation(array $data): array
    {
        // Required field check
        foreach (['check_in', 'first_name', 'last_name'] as $f) {
            if (empty(trim((string) ($data[$f] ?? '')))) {
                return ['success' => false, 'message' => "Field '{$f}' is required"];
            }
        }

        $nights = max(1, (int) ($data['nights'] ?? 1));
        $rate   = max(0, (float) ($data['rate'] ?? 0));

        $resCols = Schema::getColumnListing((new Reservation)->getTable());

        // Map all possible field names → only insert columns that exist
        $candidates = [
            'room_no'          => $data['room_no']    ?? null,
            'room_number'      => $data['room_no']    ?? null,
            'check_in'         => $data['check_in'],
            'check_out'        => $data['check_out']  ?? null,
            'nights'           => $nights,
            'adults'           => (int) ($data['adults'] ?? 1),
            'pax'              => (int) ($data['adults'] ?? 1),
            'title'            => $data['title']      ?? null,
            'first_name'       => trim($data['first_name']),
            'last_name'        => trim($data['last_name']),
            'guest_first_name' => trim($data['first_name']),
            'guest_last_name'  => trim($data['last_name']),
            'email'            => $data['email']      ?? null,
            'phone'            => $data['phone']      ?? null,
            'mobile'           => $data['phone']      ?? null,
            'rate'             => $rate,
            'source'           => $data['source']     ?? null,
            'booking_source'   => $data['source']     ?? null,
            'status'           => $data['status']     ?? 'confirmed',
            'outstanding'      => $rate * $nights,
        ];

        $insert = [];
        foreach ($candidates as $col => $val) {
            // Only set each column once (first match wins)
            if (in_array($col, $resCols) && ! array_key_exists($col, $insert)) {
                $insert[$col] = $val;
            }
        }

        if (empty($insert)) {
            return ['success' => false, 'message' => 'No matching columns found in reservations table'];
        }

        try {
            $reservation = Reservation::create($insert);
            return [
                'success'        => true,
                'message'        => 'Reservation created',
                'reservation_id' => $reservation->id,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /* ─────────────────────────────────────────────────────────────
       LIVEWIRE ACTION — cancelReservation
       Called from JS: await lwCall('cancelReservation', id)
    ───────────────────────────────────────────────────────────── */
    public function cancelReservation(int $id): array
    {
        $res = Reservation::find($id);
        if (! $res) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }

        $resCols = Schema::getColumnListing($res->getTable());

        if (in_array('status', $resCols)) {
            $res->update(['status' => 'cancelled']);
        } else {
            $res->delete(); // no status column — hard delete
        }

        return ['success' => true, 'message' => 'Booking cancelled'];
    }

    /* ─────────────────────────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────────────────────────── */
    private function safeCol($model, string $col, mixed $default = null): mixed
    {
        try {
            return $model->{$col} ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    private function mapBookingType(?string $status): string
    {
        return match ($status) {
            'confirmed', 'checked_in' => 'occupied',
            'tentative'               => 'partial',
            'waitlist'                => 'advance',
            default                   => 'occupied',
        };
    }

    private function getHotels(): array
    {
        return Hotel::all()->map(fn($h) => ['id' => $h->id, 'name' => $h->name])->toArray();
    }

    private function getRoomTypes(): array
    {
        return RoomType::all()->map(fn($t) => [
            'id'   => $t->id,
            'code' => $t->code,
            'name' => $t->name,
        ])->toArray();
    }
}
