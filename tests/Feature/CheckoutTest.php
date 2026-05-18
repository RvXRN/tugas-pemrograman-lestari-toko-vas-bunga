<?php

namespace Tests\Feature;

use App\Jobs\ProcessCheckoutJob;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_dispatches_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Mawar', 'slug' => 'mawar']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Mawar Merah',
            'slug' => 'mawar-merah',
            'description' => 'Mawar merah',
            'price' => 50000,
            'stock' => 10,
            'weight' => 500,
            'is_active' => true,
        ]);
        
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout');

        $response->assertStatus(202)
                 ->assertJsonPath('success', true);
                 
        Queue::assertPushed(ProcessCheckoutJob::class, function ($job) use ($user, $cart) {
            return $job->userId === $user->id && $job->cartId === $cart->id;
        });
    }
}
