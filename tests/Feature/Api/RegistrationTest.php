<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true; // Eseguiamo i seeder per avere i client di Passport

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

     #[Test]
    public function a_user_can_register_with_valid_data()
    {
        // 1. Arrange: Prepariamo i dati per il nuovo utente
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. Act: Eseguiamo la chiamata API per la registrazione
        $response = $this->postJson('/api/register', $userData);

        // 3. Assert: Verifichiamo che il risultato sia quello atteso
        $response->assertStatus(201); // 201 Created
        $response->assertJsonStructure(['access_token']);

        // Verifichiamo che l'utente sia stato creato nel database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    #[Test]
    public function registration_requires_password_confirmation()
    {
        // Arrange
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrong-password', // Password di conferma errata
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(422); // Errore di validazione
        $response->assertJsonValidationErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'jane.doe@example.com']);
    }
}
