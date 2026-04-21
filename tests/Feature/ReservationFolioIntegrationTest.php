<?php

namespace Tests\Feature;

use App\Models\PosOrder;
use App\Models\Reservation;
use App\Services\ReservationFolioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationFolioIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_stay_charge_into_the_reservation_folio(): void
    {
        $reservation = $this->createReservation([
            'rate' => 2500,
            'nights' => 3,
        ]);

        app(ReservationFolioService::class)->syncReservationStayCharge($reservation);

        $this->assertDatabaseHas('reservation_folios', [
            'reservation_id' => $reservation->id,
            'source' => 'reservation',
            'source_key' => 'stay_charge',
            'entry_type' => 'charge',
            'type' => 'debit',
            'amount' => 7500.00,
        ]);
    }

    public function test_it_tracks_pos_charge_tax_and_discount_in_the_folio(): void
    {
        $reservation = $this->createReservation();
        $order = PosOrder::query()->create([
            'hotel_id' => $reservation->hotel_id,
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $this->createRoom($reservation->hotel_id, $reservation->room_type_id),
            'pos_outlet_id' => $this->createOutlet($reservation->hotel_id),
            'order_number' => 'POS-1001',
            'order_type' => 'room_charge',
            'subtotal' => 1000,
            'tax_amount' => 180,
            'discount_amount' => 50,
            'grand_total' => 1130,
            'status' => 'checked_in',
            'created_by' => $this->createUser(),
        ]);

        app(ReservationFolioService::class)->syncPosOrderCharges($order->fresh(['reservation']));

        $this->assertDatabaseHas('reservation_folios', [
            'reservation_id' => $reservation->id,
            'source' => 'pos_order',
            'source_id' => $order->id,
            'source_key' => 'charge',
            'entry_type' => 'charge',
            'type' => 'debit',
            'amount' => 1000.00,
        ]);

        $this->assertDatabaseHas('reservation_folios', [
            'reservation_id' => $reservation->id,
            'source' => 'pos_order',
            'source_id' => $order->id,
            'source_key' => 'tax',
            'entry_type' => 'tax',
            'type' => 'debit',
            'amount' => 180.00,
        ]);

        $this->assertDatabaseHas('reservation_folios', [
            'reservation_id' => $reservation->id,
            'source' => 'pos_order',
            'source_id' => $order->id,
            'source_key' => 'discount',
            'entry_type' => 'discount',
            'type' => 'credit',
            'amount' => 50.00,
        ]);

    }

    private function createReservation(array $overrides = []): Reservation
    {
        $hotelGroupId = DB::table('hotel_groups')->insertGetId([
            'name' => 'Test Group',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hotelId = (string) Str::uuid();

        DB::table('hotels')->insert([
            'id' => $hotelId,
            'hotel_group_id' => $hotelGroupId,
            'name' => 'Test Hotel',
            'slug' => 'test-hotel-'.Str::lower(Str::random(6)),
            'ref_id' => (string) Str::uuid(),
            'locationable_type' => 'App\\Models\\City',
            'locationable_id' => (string) Str::uuid(),
            'total_rooms' => 10,
            'check_in_time' => '14:00:00',
            'check_out_time' => '11:00:00',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $guestId = DB::table('guests')->insertGetId([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roomTypeId = DB::table('room_types')->insertGetId([
            'code' => 'DLX-'.Str::upper(Str::random(4)),
            'name' => 'Deluxe',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reservation = Reservation::query()->create(array_merge([
            'hotel_id' => $hotelId,
            'guest_id' => $guestId,
            'room_type_id' => $roomTypeId,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'status' => 'confirmed',
            'rate' => 1500,
            'nights' => 2,
            'room_no' => '101',
        ], $overrides));

        return $reservation->fresh();
    }

    private function createRoom(string $hotelId, int $roomTypeId): string
    {
        $roomId = (string) Str::uuid();

        DB::table('hotel_rooms')->insert([
            'id' => $roomId,
            'room_type_id' => $roomTypeId,
            'room_number' => '101',
            'floor' => '1',
            'status' => 'vacant',
            'hotel_id' => $hotelId,
            'is_visible' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $roomId;
    }

    private function createOutlet(string $hotelId): int
    {
        return DB::table('pos_outlets')->insertGetId([
            'hotel_id' => $hotelId,
            'name' => 'Main Restaurant',
            'code' => 'OUT-'.Str::upper(Str::random(4)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(): int
    {
        return DB::table('users')->insertGetId([
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
