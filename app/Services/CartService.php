<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Str;

class CartService
{
    public function getCart(?string $userId, ?string $tokenGuest)
    {
        $query = Cart::with(['items.product.category', 'items.product.images']);
        
        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($tokenGuest) {
            $query->where('token_guest', $tokenGuest)->whereNull('user_id');
        } else {
            return null;
        }

        return $query->first();
    }

    public function getOrCreateCart(?string $userId, ?string $tokenGuest): Cart
    {
        $cart = $this->getCart($userId, $tokenGuest);
        
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'token_guest' => $userId ? null : ($tokenGuest ?? Str::uuid()->toString()),
            ]);
        }
        
        return $cart;
    }

    public function addItem(?string $userId, ?string $tokenGuest, int $productId, int $quantity): Cart
    {
        $cart = $this->getOrCreateCart($userId, $tokenGuest);
        $product = Product::where('id', $productId)->where('is_active', true)->firstOrFail();
        
        $item = $cart->items()->where('product_id', $productId)->first();
        
        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
        
        return $cart->load(['items.product.category', 'items.product.images']);
    }

    /**
     * Remove a cart item only if it belongs to the caller's cart.
     * Returns true on success, false if item not found or unauthorized (IDOR prevention).
     */
    public function removeItem(int $cartItemId, ?string $userId, ?string $tokenGuest): bool
    {
        // Resolve the caller's cart first
        $cart = $this->getCart($userId, $tokenGuest);

        if (!$cart) {
            return false;
        }

        // Only delete the item if it belongs to THIS cart — prevents IDOR
        $deleted = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->delete();

        return $deleted > 0;
    }
}
