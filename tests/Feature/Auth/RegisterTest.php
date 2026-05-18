<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        if ($response->status() !== 201) {
            dd($response->json());
        }

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['user', 'token']
                 ]);
                 
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'customer'
        ]);
    }

    public function test_mass_assignment_defense_on_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Evil Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'role' => 'admin' // Attempt mass assignment
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('users', [
            'email' => 'hacker@example.com',
            'role' => 'customer' // Should remain customer
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'First User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Second User',
            'email' => 'test@example.com', // Duplicate
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
