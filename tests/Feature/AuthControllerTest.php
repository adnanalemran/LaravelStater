<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase; // Resets the database after each test

    /** @test */
    public function it_can_register_a_user()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201); // Check for successful registration
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_can_login_a_user()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    /** @test */
    public function it_can_show_user_info()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/auth/showUserInfo');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => 'John Doe',
                     'email' => 'john@example.com',
                 ]);
    }

 /** @test */
public function it_can_logout_a_user()
{
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create a token for the user
    $token = $user->createToken('auth_token')->plainTextToken;

    // Set the token in the Authorization header for the request
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/v1/auth/logout');

    // Assert the response
    $response->assertStatus(200)
             ->assertJson(['message' => 'Successfully logged out']);
}


    /** @test */
    public function it_can_change_user_password()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/auth/changePassword', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password changed successfully']);

        // Assert that the new password is correctly saved
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }
}
