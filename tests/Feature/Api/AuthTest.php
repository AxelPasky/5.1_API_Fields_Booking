<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    /** @test */
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

    /** @test */
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

    /** @test */
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
}
