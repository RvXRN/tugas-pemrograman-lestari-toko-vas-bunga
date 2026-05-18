<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Layer 1: HTTP Security Headers
    // =========================================================================

    public function test_api_response_has_security_headers()
    {
        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
    }

    public function test_dangerous_proxy_headers_are_stripped()
    {
        // Attacker tries to override the URL to access admin endpoint
        $response = $this->getJson('/api/v1/catalog/products', [
            'X-Original-URL' => '/api/v1/admin/dashboard/stats',
            'X-Rewrite-URL' => '/api/v1/admin/products',
            'X-HTTP-Method-Override' => 'DELETE',
        ]);

        // Should still return catalog products (200), not admin data (403)
        $response->assertStatus(200);
    }

    public function test_api_response_is_always_json()
    {
        // Send a request with no Accept header — should still get JSON
        $response = $this->get('/api/v1/catalog/products');
        $response->assertHeader('Content-Type', 'application/json');
    }

    // =========================================================================
    // Layer 2: XSS Input Sanitization
    // =========================================================================

    public function test_xss_payload_in_name_is_sanitized_on_register()
    {
        $xssPayload = '<script>alert("xss")</script>John';

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => $xssPayload,
            'email' => 'xss@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should register successfully but name must be sanitized
        $response->assertStatus(201);
        $this->assertDatabaseMissing('users', ['name' => $xssPayload]);
        $this->assertDatabaseMissing('users', ['name' => '<script>alert("xss")</script>John']);
    }

    // =========================================================================
    // Layer 3: Rate Limiting
    // =========================================================================

    public function test_login_rate_limit_kicks_in_after_5_attempts()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'attacker@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'attacker@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    // =========================================================================
    // Layer 4: Timing Attack
    // =========================================================================

    public function test_login_returns_same_error_for_wrong_email_and_wrong_password()
    {
        $user = User::factory()->create(['email' => 'real@example.com']);

        // Wrong email
        $response1 = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // Correct email, wrong password
        $response2 = $this->postJson('/api/v1/auth/login', [
            'email' => 'real@example.com',
            'password' => 'wrongpassword',
        ]);

        // Both must return the SAME status code and error — no user enumeration
        $response1->assertStatus(422);
        $response2->assertStatus(422);
        $this->assertEquals(
            $response1->json('errors.email'),
            $response2->json('errors.email'),
            'Error messages must be identical to prevent user enumeration'
        );
    }

    // =========================================================================
    // Layer 5: Broken Access Control / IDOR
    // =========================================================================

    public function test_user_cannot_delete_another_users_cart_item()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $category = Category::create(['name' => 'Bunga', 'slug' => 'bunga-idor']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Mawar IDOR', 'slug' => 'mawar-idor',
            'description' => 'test', 'price' => 10000, 'stock' => 10,
            'weight' => 100, 'is_active' => true,
        ]);

        // User A adds an item to their cart
        $cartA = Cart::create(['user_id' => $userA->id]);
        $itemA = $cartA->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        // User B tries to delete User A's item (IDOR attack)
        $response = $this->actingAs($userB)
            ->deleteJson("/api/v1/cart/{$itemA->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('cart_items', ['id' => $itemA->id]);
    }

    public function test_inactive_product_cannot_be_added_to_cart()
    {
        $category = Category::create(['name' => 'Bunga', 'slug' => 'bunga-inactive']);
        $inactiveProduct = Product::create([
            'category_id' => $category->id, 'name' => 'Produk Nonaktif', 'slug' => 'produk-nonaktif',
            'description' => 'test', 'price' => 10000, 'stock' => 10,
            'weight' => 100, 'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
    }

    public function test_customer_cannot_access_admin_routes()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->getJson('/api/v1/admin/dashboard/stats')->assertStatus(403);
        $this->actingAs($customer)->getJson('/api/v1/admin/products')->assertStatus(403);
        $this->actingAs($customer)->getJson('/api/v1/admin/categories')->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $this->getJson('/api/v1/user')->assertStatus(401);
        $this->postJson('/api/v1/checkout')->assertStatus(401);
        $this->getJson('/api/v1/admin/dashboard/stats')->assertStatus(401);
    }
}
