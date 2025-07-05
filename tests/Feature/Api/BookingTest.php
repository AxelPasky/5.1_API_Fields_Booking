<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test; // <-- Aggiungi questo
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     * A basic feature test example.
     */
    #[Test] // <-- Modifica qui
    public function example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[Test] // <-- Modifica qui
    public function an_authenticated_user_can_create_a_booking()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create(['is_available' => true, 'price_per_hour' => 50]);

        $bookingData = [
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(0)->second(0)->toDateTimeString(),
            'end_time' => now()->addDay()->hour(11)->minute(0)->second(0)->toDateTimeString(),
        ];

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', $bookingData);

        // 3. Assert
        $response->assertStatus(201); // Created

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'field_id' => $field->id,
            'total_price' => 50.00 // 1 ora * 50€/ora
        ]);

        $response->assertJsonFragment([
            'field_id' => $field->id,
        ]);
    }

    #[Test] // <-- Modifica qui
    public function a_user_cannot_book_a_field_that_is_already_booked()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create(['is_available' => true]);

        // Creiamo una prenotazione esistente per domani dalle 10:00 alle 11:00
        Booking::factory()->create([
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(0)->second(0),
            'end_time' => now()->addDay()->hour(11)->minute(0)->second(0),
        ]);

        // L'utente tenta di prenotare dalle 10:30 alle 11:30 (sovrapposizione)
        $overlappingBookingData = [
            'field_id' => $field->id,
            'start_time' => now()->addDay()->hour(10)->minute(30)->second(0)->toDateTimeString(),
            'end_time' => now()->addDay()->hour(11)->minute(30)->second(0)->toDateTimeString(),
        ];

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', $overlappingBookingData);

        // 3. Assert
        $response->assertStatus(422); // Unprocessable Entity (errore di validazione)
        $response->assertJsonValidationErrors('start_time');
    }

    #[Test]
    public function an_authenticated_user_can_view_their_own_bookings()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;

        // Creiamo 2 prenotazioni per questo utente
        Booking::factory()->count(2)->create(['user_id' => $user->id]);
        // Creiamo 1 prenotazione per un altro utente, che non deve essere visibile
        Booking::factory()->create();

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings');

        // 3. Assert
        $response->assertStatus(200);
        // Verifichiamo che la risposta contenga esattamente 2 prenotazioni
        $response->assertJsonCount(2, 'data');
        // Verifichiamo che la prima prenotazione restituita appartenga all'utente corretto
        $response->assertJsonPath('data.0.user_id', $user->id);
    }

    #[Test]
    public function an_authenticated_user_can_delete_their_own_booking()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/bookings/' . $booking->id);

        // 3. Assert
        $response->assertStatus(204); // No Content
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_booking()
    {
        // 1. Arrange
        $owner = User::factory()->create(); // Il proprietario della prenotazione
        $attacker = User::factory()->create(); // L'utente che tenta di cancellare
        $token = $attacker->createToken('auth-token')->accessToken;

        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/bookings/' . $booking->id);

        // 3. Assert
        $response->assertStatus(403); // Forbidden
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function an_authenticated_user_can_view_a_single_booking()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings/' . $booking->id);

        // 3. Assert
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
        // 1. Arrange
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $token = $viewer->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings/' . $booking->id);

        // 3. Assert
        $response->assertStatus(403); // O 404, a seconda della policy
    }

    #[Test]
    public function an_authenticated_user_can_update_their_own_booking()
    {
        // 1. Arrange
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

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/bookings/' . $booking->id, $updateData);

        // 3. Assert
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
        // 1. Arrange
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $token = $attacker->createToken('auth-token')->accessToken;
        $booking = Booking::factory()->create(['user_id' => $owner->id]);

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/bookings/' . $booking->id, ['start_time' => now()->addDay()->toDateTimeString()]);

        // 3. Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_calculate_the_price_for_a_booking_slot()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create(['price_per_hour' => 50.00]);

        $bookingData = [
            'field_id' => $field->id,
            // Prenotazione di 2 ore
            'start_time' => now()->addDay()->hour(10)->minute(0)->second(0)->toDateTimeString(),
            'end_time' => now()->addDay()->hour(12)->minute(0)->second(0)->toDateTimeString(),
        ];

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings/calculate-price', $bookingData);

        // 3. Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'total_price' => 100.00 // 2 ore * 50 €/ora
            ]
        ]);
    }
}
