<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_can_login_with_correct_credentials()
    {
        // Arrange
        $password = 'my-secret-password';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
        ]);
    }

    #[Test]
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
        $response->assertStatus(401);
        $this->assertGuest('api');
    }

    #[Test]
    public function a_logged_in_user_can_logout()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $user->tokens->first()->id,
            'revoked' => true,
        ]);
    }

    #[Test]
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
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;
        $updateData = [
            'name' => 'New Name',
            'email' => 'new.email@example.com',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $updateData
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new.email@example.com',
        ]);
    }

    #[Test]
    public function updating_profile_with_an_existing_email_fails()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['email' => 'existing@example.com']);
        $token = $user1->createToken('auth-token')->accessToken;
        $updateData = [
            'name' => 'Another Name',
            'email' => 'existing@example.com',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    #[Test]
    public function a_user_can_register_with_valid_data()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure(['access_token']);
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
            'password_confirmation' => 'wrong-password',
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'jane.doe@example.com']);
    }
}
