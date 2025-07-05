<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test; // <-- Aggiungi
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indica se il seeder predefinito deve essere eseguito prima di ogni test.
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[Test] // <-- Modifica
    public function a_user_can_login_with_correct_credentials()
    {
        // 1. Arrange: Prepariamo l'ambiente creando un utente
        $password = 'my-secret-password';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        // 2. Act: Eseguiamo l'azione da testare, la chiamata API
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // 3. Assert: Verifichiamo che il risultato sia quello atteso
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
        ]);
    }

    #[Test] // <-- Modifica
    public function a_user_cannot_login_with_incorrect_credentials()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertStatus(401); // Unauthorized
        $this->assertGuest('api');
    }

    #[Test] // <-- Modifica
    public function a_logged_in_user_can_logout()
    {
        // 1. Arrange: Creiamo e autentichiamo un utente
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;

        // 2. Act: Eseguiamo la chiamata API per il logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // 3. Assert: Verifichiamo che il logout sia andato a buon fine
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);

        // Verifichiamo che il token sia stato revocato
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $user->tokens->first()->id,
            'revoked' => true,
        ]);
    }

    #[Test] // <-- Modifica
    public function an_authenticated_user_can_fetch_their_details()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        // Assert
        $response->assertStatus(200);
        // Modifica questa asserzione per cercare dentro il wrapper 'data'
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ]
        ]);
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
