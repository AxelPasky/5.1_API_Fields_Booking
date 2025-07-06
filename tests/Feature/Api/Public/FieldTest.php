<?php
namespace Tests\Feature\Api\Public;

use App\Models\Field;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FieldTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    #[Test]
    public function a_regular_user_can_only_see_available_fields()
    {
        // Arrange
        $availableField = Field::factory()->create(['is_available' => true]);
        $unavailableField = Field::factory()->create(['is_available' => false]);
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $availableField->name]);
        $fieldIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($unavailableField->id, $fieldIds);
    }

    #[Test]
    public function an_admin_user_can_see_all_fields()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $token = $admin->createToken('auth-token')->accessToken;
        $availableField = Field::factory()->create(['is_available' => true]);
        $unavailableField = Field::factory()->create(['is_available' => false]);
        $totalFields = Field::count();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $availableField->name]);
        $response->assertJsonFragment(['name' => $unavailableField->name]);
        $response->assertJsonCount($totalFields, 'data');
    }

    #[Test]
    public function an_authenticated_user_can_view_a_single_field()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields/' . $field->id);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type,
            ]
        ]);
    }

    #[Test]
    public function an_admin_can_create_a_new_field()
    {
        // Arrange
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;
        $fieldData = [
            'name' => 'New Football Pitch',
            'type' => 'football',
            'price_per_hour' => 50.00,
            'is_available' => true,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/fields', $fieldData);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('fields', $fieldData);
        $response->assertJsonFragment($fieldData);
    }

    #[Test]
    public function a_regular_user_cannot_create_a_field()
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;
        $fieldData = [
            'name' => 'Unauthorized Pitch',
            'is_available' => true,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/fields', $fieldData);

        // Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function an_admin_can_update_a_field()
    {
        // Arrange
        $field = Field::factory()->create();
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;
        $updateData = [
            'name' => 'Updated Field Name',
            'type' => 'padel',
            'price_per_hour' => 99.99,
            'is_available' => false,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/fields/' . $field->id, $updateData);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('fields', $updateData);
        $response->assertJsonFragment($updateData);
    }

    #[Test]
    public function a_regular_user_cannot_update_a_field()
    {
        // Arrange
        $field = Field::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/fields/' . $field->id, ['name' => 'Attempt to update']);

        // Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function an_admin_can_delete_a_field()
    {
        // Arrange
        $field = Field::factory()->create();
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/admin/fields/' . $field->id);

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('fields', ['id' => $field->id]);
    }

    #[Test]
    public function a_regular_user_cannot_delete_a_field()
    {
        // Arrange
        $field = Field::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/admin/fields/' . $field->id);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseHas('fields', ['id' => $field->id]);
    }

    #[Test]
    public function it_returns_available_time_slots_for_a_given_date()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create();
        $date = Carbon::tomorrow();
        Booking::factory()->create([
            'field_id' => $field->id,
            'start_time' => $date->copy()->hour(14),
            'end_time' => $date->copy()->hour(16),
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/fields/{$field->id}/availability?date=" . $date->toDateString());

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['time' => '10:00', 'available' => true]);
        $response->assertJsonFragment(['time' => '14:00', 'available' => false]);
        $response->assertJsonFragment(['time' => '15:00', 'available' => false]);
        $response->assertJsonFragment(['time' => '16:00', 'available' => true]);
    }
}