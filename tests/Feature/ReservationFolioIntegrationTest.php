<?php

namespace Tests\Feature;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Models\PosOrder;
use App\Models\Reservation;
use App\Models\ReservationRoom;
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
        $reservationRoom = $this->createReservationRoom($reservation);
        $order = PosOrder::query()->create([
            'hotel_id' => $reservation->hotel_id,
            'reservation_id' => $reservation->id,
            'reservation_room_id' => $reservationRoom->id,
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
            'reservation_room_id' => $reservationRoom->id,
            'source' => 'pos_order',
            'source_id' => $order->id,
            'source_key' => 'charge',
            'entry_type' => 'charge',
            'type' => 'debit',
            'amount' => 1000.00,
        ]);

        $this->assertDatabaseHas('reservation_folios', [
            'reservation_id' => $reservation->id,
            'reservation_room_id' => $reservationRoom->id,
            'source' => 'pos_order',
            'source_id' => $order->id,
            'source_key' => 'tax',
            'entry_type' => 'tax',
            'type' => 'debit',
            'amount' => 180.00,
        ]);

        $this->assertDatabaseHas('reservation_folios', [
            'reservation_id' => $reservation->id,
            'reservation_room_id' => $reservationRoom->id,
            'source' => 'pos_order',
            'source_id' => $order->id,
            'source_key' => 'discount',
            'entry_type' => 'discount',
            'type' => 'credit',
            'amount' => 50.00,
        ]);

    }

    public function test_room_posting_create_data_uses_the_selected_room_detail_guest(): void
    {
        $reservation = $this->createReservation(['guest_id' => null]);
        $guestId = DB::table('guests')->where('email', 'john@example.com')->value('id');
        $roomId = $this->createRoom($reservation->hotel_id, $reservation->room_type_id);

        DB::table('reservation_guests')->insert([
            'reservation_id' => $reservation->id,
            'guest_id' => $guestId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = DB::table('reservation_room_categories')->insertGetId([
            'reservation_id' => $reservation->id,
            'room_type_id' => $reservation->room_type_id,
            'meal_plan_id' => null,
            'rooms_count' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detailId = DB::table('reservation_room_details')->insertGetId([
            'category_id' => $categoryId,
            'room_number' => '101',
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'status' => 'checked_in',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = PosOrderResource::prepareCreateData([
            'order_type' => 'room_charge',
            'reservation_room_detail_id' => $detailId,
            'items' => [
                [
                    'price' => 100,
                    'quantity' => 2,
                    'tax_amount' => 18,
                ],
            ],
            'discount_amount' => 0,
            'status' => 'draft',
        ]);

        $this->assertSame($reservation->id, $data['reservation_id']);
        $this->assertSame($guestId, $data['guest_id']);
        $this->assertSame($roomId, $data['room_id']);
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

    private function createReservationRoom(Reservation $reservation): ReservationRoom
    {
        return ReservationRoom::query()->create([
            'reservation_id' => $reservation->id,
            'room_type_id' => $reservation->room_type_id,
            'room_number' => $reservation->room_no,
            'check_in' => $reservation->check_in,
            'check_out' => $reservation->check_out,
            'rate' => $reservation->rate,
            'nights' => $reservation->nights,
            'adults' => 2,
            'children' => 0,
            'infant' => 0,
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);
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
