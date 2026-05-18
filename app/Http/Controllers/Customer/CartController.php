<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use ApiResponseTrait;

    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    protected function getIdentifiers(Request $request): array
    {
        return [
            'user_id' => $request->user()?->id,
            'token_guest' => $request->header('X-Guest-Token'),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        ['user_id' => $userId, 'token_guest' => $tokenGuest] = $this->getIdentifiers($request);
        
        if (!$userId && !$tokenGuest) {
            return $this->successResponse(['items' => []], 'Keranjang kosong', 200)->withHeaders(['X-Guest-Token' => Str::uuid()->toString()]);
        }

        $cart = $this->cartService->getCart($userId, $tokenGuest);

        return $this->successResponse($cart ? $cart->toArray() : ['items' => []], 'Data keranjang');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        ['user_id' => $userId, 'token_guest' => $tokenGuest] = $this->getIdentifiers($request);
        
        if (!$userId && !$tokenGuest) {
            $tokenGuest = Str::uuid()->toString();
        }

        $cart = $this->cartService->addItem($userId, $tokenGuest, $request->product_id, $request->quantity);

        $headers = $userId ? [] : ['X-Guest-Token' => $cart->token_guest];
        
        return $this->successResponse($cart->toArray(), 'Berhasil menambah ke keranjang', 201)->withHeaders($headers);
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        ['user_id' => $userId, 'token_guest' => $tokenGuest] = $this->getIdentifiers($request);

        $removed = $this->cartService->removeItem($itemId, $userId, $tokenGuest);

        if (!$removed) {
            return $this->errorResponse('Item tidak ditemukan atau bukan milik Anda.', 403);
        }

        return $this->successResponse(null, 'Item dihapus dari keranjang');
    }
}
