<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
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

     #[Test]
    public function an_authenticated_user_can_update_their_profile()
    {
        // 1. Arrange: Creiamo e autentichiamo un utente
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;

        $updateData = [
            'name' => 'New Name',
            'email' => 'new.email@example.com',
        ];

        // 2. Act: Eseguiamo la chiamata API per aggiornare il profilo
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/user', $updateData);

        // 3. Assert: Verifichiamo che la risposta sia corretta
        $response->assertStatus(200);
        // Usa assertJson per verificare che la struttura esista nella risposta
        $response->assertJson([
            'data' => $updateData
        ]);

        // Verifichiamo che i dati nel database siano stati aggiornati
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new.email@example.com',
        ]);
    }

     #[Test]
    public function updating_profile_with_an_existing_email_fails()
    {
        // Arrange: Creiamo due utenti
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['email' => 'existing@example.com']);
        $token = $user1->createToken('auth-token')->accessToken;

        $updateData = [
            'name' => 'Another Name',
            'email' => 'existing@example.com', // Email già in uso da user2
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(422); // Errore di validazione
        $response->assertJsonValidationErrors('email');
    }
}
