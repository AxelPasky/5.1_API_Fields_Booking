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
}
