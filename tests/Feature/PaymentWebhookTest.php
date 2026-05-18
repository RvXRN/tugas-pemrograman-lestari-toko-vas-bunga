<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.midtrans.server_key', 'dummy-server-key');
    }

    public function test_webhook_rejects_invalid_signature()
    {
        $payload = [
            'order_id' => 'INV-12345',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => 'invalid-hash-string',
        ];

        $response = $this->postJson('/api/v1/payments/webhook', $payload);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Invalid signature']);
    }

    public function test_webhook_accepts_valid_signature_and_updates_order()
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'INV-2023-XYZ',
            'total_amount' => 50000,
            'status' => 'pending'
        ]);

        $serverKey = config('services.midtrans.server_key');
        $validSignature = hash("sha512", $order->order_number . "200" . "50000.00" . $serverKey);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'abc-123',
            'payment_type' => 'gopay',
        ];

        $response = $this->postJson('/api/v1/payments/webhook', $payload);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'transaction_id' => 'abc-123',
            'status' => 'success'
        ]);
    }
}
