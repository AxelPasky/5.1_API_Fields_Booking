<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Booking;
use App\Models\User;
use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    #[Test]
    public function a_regular_user_cannot_access_admin_statistics()
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->accessToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/statistics/revenue');

        // Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function an_admin_can_view_total_revenue_statistics()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $token = $admin->createToken('auth-token')->accessToken;

       
        Booking::factory()->create(['total_price' => 100]);
        Booking::factory()->create(['total_price' => 150]);
        Booking::factory()->create(['total_price' => 75]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/statistics/revenue');

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'total_revenue' => 325.00 
            ]
        ]);
    }

    #[Test]
    public function an_admin_can_view_field_performance_statistics()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $token = $admin->createToken('auth-token')->accessToken;

        $popularField = Field::factory()->create(['name' => 'Campo Popolare']);
        $unpopularField = Field::factory()->create(['name' => 'Campo Meno Popolare']);

        Booking::factory()->count(3)->create(['field_id' => $popularField->id]);
        Booking::factory()->create(['field_id' => $unpopularField->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/statistics/field-performance');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', $popularField->name);
        $response->assertJsonPath('data.0.bookings_count', 3);
        $response->assertJsonPath('data.1.name', $unpopularField->name);
        $response->assertJsonPath('data.1.bookings_count', 1);
    }
}