<?php

namespace App\Jobs;

use App\Events\CheckoutStatusUpdated;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessCheckoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $userId;
    public int $cartId;

    public function __construct(string $userId, int $cartId)
    {
        $this->userId = $userId;
        $this->cartId = $cartId;
    }

    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $cart = Cart::with('items')->where('id', $this->cartId)->first();

                if (!$cart || $cart->items->isEmpty()) {
                    throw new \Exception("Keranjang tidak ditemukan atau kosong.");
                }

                $productIds = $cart->items->pluck('product_id')->toArray();
                
                // Pessimistic Locking: Lock products being purchased
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                $totalAmount = 0;
                $orderItems = [];

                foreach ($cart->items as $item) {
                    $product = $products->get($item->product_id);

                    if (!$product || $product->stock < $item->quantity) {
                        $productName = $product ? $product->name : 'Unknown';
                        throw new \Exception("Stok produk '{$productName}' tidak mencukupi.");
                    }

                    // Deduct stock
                    $product->stock -= $item->quantity;
                    $product->save();

                    $totalAmount += $product->price * $item->quantity;
                    
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'price' => $product->price,
                        'quantity' => $item->quantity,
                    ];
                }

                // Create Order
                $orderNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                $order = Order::create([
                    'user_id' => $this->userId,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount,
                    'status' => 'pending', // Waiting for payment
                ]);

                // Create Order Items
                foreach ($orderItems as $orderItem) {
                    $orderItem['order_id'] = $order->id;
                    $order->items()->create($orderItem);
                }

                // Get Midtrans Snap Token
                $midtransService = app(MidtransService::class);
                $snapToken = $midtransService->createSnapTransaction($order);

                $order->snap_token_midtrans = $snapToken;
                $order->save();

                // Delete Cart
                $cart->items()->delete();
                $cart->delete();

                // Broadcast Success
                event(new CheckoutStatusUpdated(
                    $this->userId, 
                    'success', 
                    'Checkout berhasil. Silakan selesaikan pembayaran.', 
                    $order->load('items.product')->toArray()
                ));
            });
        } catch (\Exception $e) {
            // Broadcast Failed
            event(new CheckoutStatusUpdated(
                $this->userId, 
                'failed', 
                $e->getMessage()
            ));
        }
    }
}
