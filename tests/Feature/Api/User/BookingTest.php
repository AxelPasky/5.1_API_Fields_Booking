<?php
namespace Tests\Feature\Api\User;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_authenticated_user_can_create_a_booking()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create(['is_available' => true, 'price_per_hour' => 50]);
        $bookingData = [
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(0)->second(0)->toDateTimeString(),
            'end_time' => now()->addDay()->hour(11)->minute(0)->second(0)->toDateTimeString(),
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', $bookingData);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'field_id' => $field->id,
            'total_price' => 50.00
        ]);
        $response->assertJsonFragment([
            'field_id' => $field->id,
        ]);
    }

    #[Test]
    public function a_user_cannot_book_a_field_that_is_already_booked()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create(['is_available' => true]);
        Booking::factory()->create([
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(0)->second(0),
            'end_time' => now()->addDay()->hour(11)->minute(0)->second(0),
        ]);
        $overlappingBookingData = [
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(30)->second(0)->toDateTimeString(),
            'end_time' => now()->addDay()->hour(11)->minute(30)->second(0)->toDateTimeString(),
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', $overlappingBookingData);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_time');
    }

    #[Test]
    public function an_authenticated_user_can_view_their_own_bookings()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        Booking::factory()->count(2)->create(['user_id' => $user->id]);
        Booking::factory()->create();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.user_id', $user->id);
    }

    #[Test]
    public function an_authenticated_user_can_delete_their_own_booking()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/bookings/' . $booking->id);

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_booking()
    {
        // Arrange
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $token = $attacker->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/bookings/' . $booking->id);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function an_authenticated_user_can_view_a_single_booking()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings/' . $booking->id);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $booking->id,
                'user_id' => $user->id,
            ]
        ]);
    }

    #[Test]
    public function a_user_cannot_view_another_users_booking()
    {
        // Arrange
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $token = $viewer->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings/' . $booking->id);

        // Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function an_authenticated_user_can_update_their_own_booking()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'start_time' => now()->addDays(2)->hour(10),
            'end_time' => now()->addDays(2)->hour(11),
        ]);
        $updateData = [
            'start_time' => now()->addDays(3)->hour(14)->minute(0)->second(0)->toDateTimeString(),
            'end_time' => now()->addDays(3)->hour(15)->minute(0)->second(0)->toDateTimeString(),
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/bookings/' . $booking->id, $updateData);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'start_time' => $updateData['start_time'],
        ]);
        $response->assertJsonFragment(['start_time' => $updateData['start_time']]);
    }

    #[Test]
    public function a_user_cannot_update_another_users_booking()
    {
        // Arrange
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $token = $attacker->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/bookings/' . $booking->id, ['start_time' => now()->addDay()->toDateTimeString()]);

        // Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_calculate_the_price_for_a_booking_slot()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create(['price_per_hour' => 50.00]);
        $bookingData = [
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(0)->second(0)->toDateTimeString(),
            'end_time' => now()->addDay()->hour(12)->minute(0)->second(0)->toDateTimeString(),
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings/calculate-price', $bookingData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'total_price' => 100.00
            ]
        ]);
    }
}
