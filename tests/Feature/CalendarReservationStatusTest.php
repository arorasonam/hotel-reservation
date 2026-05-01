<?php

namespace Tests\Feature;

use App\Filament\Pages\Reservations;
use App\Models\bookingSource;
use App\Models\BookingType;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\HotelGroup;
use App\Models\HotelRoom;
use App\Models\Reservation;
use App\Models\ReservationRoomCategory;
use App\Models\ReservationRoomDetail;
use App\Models\RoomType;
use App\Models\SourceMarket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarReservationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_room_check_in_updates_room_detail_physical_room_and_parent_reservation(): void
    {
        [$reservation, $firstDetail] = $this->createReservationWithRooms(['101']);

        $result = (new Reservations)->updateRoomStatusInBooking($firstDetail->id, 'checked_in');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('reservation_room_details', [
            'id' => $firstDetail->id,
            'status' => 'checked_in',
        ]);
        $this->assertDatabaseHas('hotel_rooms', [
            'room_number' => '101',
            'status' => 'occupied',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_in',
        ]);
    }

    public function test_calendar_group_check_in_updates_every_room_for_the_reservation(): void
    {
        [$reservation] = $this->createReservationWithRooms(['101', '102']);

        $result = (new Reservations)->updateReservationStatus($reservation->id, 'checked_in');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_in',
        ]);
        $this->assertDatabaseMissing('reservation_room_details', [
            'category_id' => $reservation->roomCategories()->first()->id,
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('hotel_rooms', [
            'room_number' => '101',
            'status' => 'occupied',
        ]);
        $this->assertDatabaseHas('hotel_rooms', [
            'room_number' => '102',
            'status' => 'occupied',
        ]);
    }

    /**
     * @return array{0: Reservation, 1: ReservationRoomDetail}
     */
    private function createReservationWithRooms(array $roomNumbers): array
    {
        $country = Country::create([
            'iso2' => 'IN',
            'iso3' => 'IND',
            'capital' => 'New Delhi',
            'region' => 'Asia',
            'name' => 'India',
            'subregion' => 'Southern Asia',
            'timezones' => [],
            'emoji' => 'IN',
            'currency' => 'INR',
            'ref_id' => 1,
        ]);

        $hotelGroup = HotelGroup::create(['name' => 'Test Group']);

        $hotel = Hotel::create([
            'hotel_group_id' => $hotelGroup->id,
            'name' => 'Test Hotel',
            'locationable_id' => $country->id,
            'locationable_type' => Country::class,
        ]);

        $roomType = RoomType::create([
            'code' => 'DLX',
            'name' => 'Deluxe',
        ]);

        $bookingSource = bookingSource::create([
            'hotel_id' => $hotel->id,
            'name' => 'Direct',
        ]);

        $bookingType = BookingType::create([
            'hotel_id' => $hotel->id,
            'name' => 'FIT',
        ]);

        $sourceMarket = SourceMarket::create([
            'hotel_id' => $hotel->id,
            'name' => 'Domestic',
        ]);

        foreach ($roomNumbers as $roomNumber) {
            HotelRoom::create([
                'hotel_id' => $hotel->id,
                'room_type_id' => $roomType->id,
                'room_number' => $roomNumber,
                'floor' => '1',
                'status' => 'vacant',
                'is_visible' => true,
            ]);
        }

        $reservation = Reservation::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'check_in' => '2026-05-01',
            'check_out' => '2026-05-02',
            'status' => 'confirmed',
            'rate' => 0,
            'nights' => 1,
            'booking_source_id' => $bookingSource->id,
            'booking_type_id' => $bookingType->id,
            'source_market_id' => $sourceMarket->id,
        ]);

        $category = ReservationRoomCategory::create([
            'reservation_id' => $reservation->id,
            'room_type_id' => $roomType->id,
            'rooms_count' => count($roomNumbers),
        ]);

        $details = collect($roomNumbers)->map(fn (string $roomNumber) => ReservationRoomDetail::create([
            'category_id' => $category->id,
            'room_number' => $roomNumber,
            'status' => 'confirmed',
        ]));

        return [$reservation, $details->first()];
    }
}
