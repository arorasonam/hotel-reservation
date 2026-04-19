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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /* ─────────────────────────────────────────────────────────────
       VIEW DATA
    ───────────────────────────────────────────────────────────── */
    public function getViewData(): array
    {
        $groupedRooms = RoomType::with('rooms')->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'code'       => $type->code,
                'label'      => $type->name,
                'totalRooms' => $type->rooms()->whereIn('status', ['clean', 'vacant'])->count(),
                'rooms'      => $type->rooms->map(fn($room) => [
                    'room_number' => (string) $room->room_number, // Cast to string for JS comparison
                    'status'      => strtolower(trim($room->status ?? 'dirty')),
                ])->toArray(),
            ];
        });
        return [
            'hotels'       => Hotel::all()->map(fn($h) => ['id' => $h->id, 'name' => $h->name])->toArray(),
            'roomTypes'    => RoomType::all()->map(fn($t) => ['id' => $t->id, 'code' => $t->code, 'name' => $t->name])->toArray(),
            'groupedRooms' => $groupedRooms,
            'totalVacant'  => $this->countVacantRooms(),
            'reservations' => $this->loadReservations(),
        ];
    }

    private function countVacantRooms(): int
    {
        // Requirement: Only count rooms marked as 'clean' or 'vacant'
        return HotelRoom::whereIn('status', ['clean', 'vacant'])->count();
    }

    // private function loadReservations(): array
    // {
    //     return Reservation::with(['reservationGuests'])
    //         ->whereIn('status', ['confirmed', 'tentative', 'waitlist', 'checked_in'])
    //         ->get()
    //         ->map(function ($res) {
    //             $primary = $res->reservationGuests->where('is_primary', true)->first()
    //                 ?? $res->reservationGuests->first();

    //             return [
    //                 'id'             => $res->id,
    //                 'reservation_id' => $res->reservation_number,
    //                 'room_no'        => trim((string) $res->room_no), // Force string to match grid labels
    //                 'first_name'     => $primary?->first_name ?? 'Guest',
    //                 'last_name'      => $primary?->last_name ?? '',
    //                 'check_in'       => $res->check_in ? Carbon::parse($res->check_in)->format('Y-m-d') : null,
    //                 'check_out'      => $res->check_out ? Carbon::parse($res->check_out)->format('Y-m-d') : null,
    //                 'nights'         => (int) ($res->nights ?? 1),
    //                 'status'         => $res->status,
    //                 'booking_type'   => $this->mapBookingType($res->status),
    //                 'verified'       => in_array($res->status, ['confirmed', 'checked_in']),
    //                 'room_type_id' => $res->room_type_id,
    //             ];
    //         })->toArray();
    // }

    private function loadReservations(): array
    {
        return Reservation::with(['reservationGuests', 'reservationRooms'])
            ->whereIn(\DB::raw('LOWER(status)'), ['confirmed', 'tentative', 'checked_in', 'booked'])
            ->get()
            ->flatMap(function ($res) {
                $primary = $res->reservationGuests->where('is_primary', true)->first()
                    ?? $res->reservationGuests->first();
                return $res->reservationRooms->map(function ($roomBlock) use ($res, $primary) {
                    return [
                        'id'             => $res->id, // Parent ID for Edit/View links
                        'room_stay_id'   => $roomBlock->id, // Specific room ID for partial check-in
                        'reservation_id' => $res->reservation_number,
                        'room_no' => trim((string) $roomBlock->room_number),
                        'first_name'     => $primary?->first_name ?? 'Guest',
                        'last_name'      => $primary?->last_name ?? '',
                        'check_in'       => $res->check_in,
                        'check_out'      => $res->check_out,
                        'nights'         => (int) $res->nights,
                        // Use the status of the individual room, not the whole booking
                        'status'         => $roomBlock->status ?? $res->status,
                        'booking_type'   => $this->mapBookingType($roomBlock->status ?? $res->status),
                        'room_type_id'   => $roomBlock->room_type_id,
                    ];
                });
            })->toArray();
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

    // private function countVacantRooms(): int
    // {
    //     try {
    //         $cols = Schema::getColumnListing((new HotelRoom)->getTable());
    //         return in_array('status', $cols)
    //             ? HotelRoom::whereIn('status', ['clean', 'vacant'])->count()
    //             : HotelRoom::count();
    //     } catch (\Throwable) {
    //         return 0;
    //     }
    // }

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

    public function updateRoomStatusInBooking(int $roomStayId, string $status): array
    {
        try {
            $roomBlock = \App\Models\ReservationRoom::find($roomStayId);
            if (!$roomBlock) return ['success' => false, 'message' => 'Room block not found'];

            // 1. Update the individual room status
            $roomBlock->update(['status' => $status]);

            // 2. Sync physical room status
            if ($roomBlock->room_number) {
                $physicalStatus = ($status === 'checked_in') ? 'occupied' : 'dirty';
                \App\Models\HotelRoom::where('room_number', $roomBlock->room_number)->update(['status' => $physicalStatus]);
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

    public function updateReservationStatus(int $id, string $status): array
    {
        $reservation = Reservation::find($id);
        if (!$reservation) return ['success' => false, 'message' => 'Reservation not found'];

        try {
            // 1. Update Reservation Status
            $reservation->update(['status' => $status]);

            // 2. Automatically update the Room Status based on action
            if ($reservation->room_no) {
                $roomStatus = ($status === 'checked_in') ? 'occupied' : 'dirty';
                HotelRoom::where('room_number', $reservation->room_no)->update(['status' => $roomStatus]);
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
