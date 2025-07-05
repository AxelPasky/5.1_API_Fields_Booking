<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\User;
use App\Models\Booking; // <-- Aggiungi questo
use Carbon\Carbon; // <-- Aggiungi questo
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test; // <-- Aggiungi
use Tests\TestCase;

class FieldTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    #[Test] // <-- Modifica
    public function a_regular_user_can_only_see_available_fields()
    {
        // 1. Arrange
        // Creiamo un campo disponibile e uno non disponibile
        $availableField = Field::factory()->create(['is_available' => true]);
        $unavailableField = Field::factory()->create(['is_available' => false]);

        // Creiamo e autentichiamo un utente normale
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields');

        // 3. Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $availableField->name]);
        $response->assertJsonMissing(['name' => $unavailableField->name]);
    }

    #[Test] // <-- Modifica
    public function an_admin_user_can_see_all_fields()
    {
        // 1. Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $token = $admin->createToken('auth-token')->accessToken;

        $availableField = Field::factory()->create(['is_available' => true]);
        $unavailableField = Field::factory()->create(['is_available' => false]);
        
        $totalFields = Field::count(); // <-- Aggiungi questa riga per contare tutti i campi

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields');

        // 3. Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $availableField->name]);
        $response->assertJsonFragment(['name' => $unavailableField->name]);
        $response->assertJsonCount($totalFields, 'data'); // <-- Modifica questa riga
    }

    #[Test] // <-- Modifica
    public function an_authenticated_user_can_view_a_single_field()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create();

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields/' . $field->id);

        // 3. Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type,
            ]
        ]);
    }

    #[Test] // <-- Modifica
    public function an_admin_can_create_a_new_field()
    {
        // 1. Arrange
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;

        $fieldData = [
            'name' => 'New Football Pitch',
            'type' => 'football',
            'price_per_hour' => 50.00, // <-- Aggiungi questa riga
            'is_available' => true,
        ];

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/fields', $fieldData);

        // 3. Assert
        $response->assertStatus(201); // Created
        $this->assertDatabaseHas('fields', $fieldData);
        $response->assertJsonFragment($fieldData);
    }

    #[Test] // <-- Modifica
    public function a_regular_user_cannot_create_a_field()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        $fieldData = [
            'name' => 'Unauthorized Pitch',
            'is_available' => true,
        ];

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/fields', $fieldData);

        // 3. Assert
        $response->assertStatus(403); // Forbidden
    }

    #[Test] // <-- Modifica
    public function an_admin_can_update_a_field()
    {
        // 1. Arrange
        $field = Field::factory()->create();
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;

        $updateData = [
            'name' => 'Updated Field Name',
            'type' => 'padel', // <-- Cambiato da 'updated_type' a un valore valido
            'price_per_hour' => 99.99,
            'is_available' => false,
        ];

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/fields/' . $field->id, $updateData);

        // 3. Assert
        $response->assertStatus(200); // OK
        $this->assertDatabaseHas('fields', $updateData);
        $response->assertJsonFragment($updateData);
    }

    #[Test] // <-- Modifica
    public function a_regular_user_cannot_update_a_field()
    {
        // 1. Arrange
        $field = Field::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/fields/' . $field->id, ['name' => 'Attempt to update']);

        // 3. Assert
        $response->assertStatus(403); // Forbidden
    }

    #[Test] // <-- Modifica
    public function an_admin_can_delete_a_field()
    {
        // 1. Arrange
        $field = Field::factory()->create();
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/admin/fields/' . $field->id);

        // 3. Assert
        $response->assertStatus(204); // No Content
        $this->assertDatabaseMissing('fields', ['id' => $field->id]);
    }

    #[Test] // <-- Modifica
    public function a_regular_user_cannot_delete_a_field()
    {
        // 1. Arrange
        $field = Field::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('User');
        $token = $user->createToken('auth-token')->accessToken;

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/admin/fields/' . $field->id);

        // 3. Assert
        $response->assertStatus(403); // Forbidden
        $this->assertDatabaseHas('fields', ['id' => $field->id]);
    }

    #[Test]
    public function it_returns_available_time_slots_for_a_given_date()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $field = Field::factory()->create();
        $date = Carbon::tomorrow();

        // Prenotazione esistente dalle 14:00 alle 16:00
        Booking::factory()->create([
            'field_id' => $field->id,
            'start_time' => $date->copy()->hour(14),
            'end_time' => $date->copy()->hour(16),
        ]);

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/fields/{$field->id}/availability?date=" . $date->toDateString());

        // 3. Assert
        $response->assertStatus(200);
        // Lo slot delle 10:00 dovrebbe essere disponibile
        $response->assertJsonFragment(['time' => '10:00', 'available' => true]);
        // Lo slot delle 14:00 dovrebbe essere occupato
        $response->assertJsonFragment(['time' => '14:00', 'available' => false]);
        // Lo slot delle 15:00 dovrebbe essere occupato
        $response->assertJsonFragment(['time' => '15:00', 'available' => false]);
        // Lo slot delle 16:00 dovrebbe essere di nuovo disponibile
        $response->assertJsonFragment(['time' => '16:00', 'available' => true]);
    }
}
