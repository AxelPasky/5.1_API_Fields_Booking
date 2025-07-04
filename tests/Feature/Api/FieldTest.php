<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /** @test */
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

    /** @test */
    public function an_admin_user_can_see_all_fields()
    {
        // 1. Arrange
        // Creiamo un campo disponibile e uno non disponibile
        $availableField = Field::factory()->create(['is_available' => true]);
        $unavailableField = Field::factory()->create(['is_available' => false]);

        // Creiamo e autentichiamo un utente admin
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('auth-token')->accessToken;

        // 2. Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/fields');

        // 3. Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $availableField->name]);
        $response->assertJsonFragment(['name' => $unavailableField->name]);
    }
}
