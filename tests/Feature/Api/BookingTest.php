<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
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
}
