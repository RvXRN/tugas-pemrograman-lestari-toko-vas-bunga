<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Mawar', 'slug' => 'mawar']);
        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Mawar Merah',
            'slug' => 'mawar-merah',
            'description' => 'Mawar merah',
            'price' => 50000,
            'stock' => 10,
            'weight' => 500,
            'is_active' => true,
        ]);
    }

    public function test_guest_can_add_to_cart()
    {
        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
                 ->assertHeader('X-Guest-Token');
                 
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_can_add_to_cart()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/cart', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', ['quantity' => 3]);
    }
}
