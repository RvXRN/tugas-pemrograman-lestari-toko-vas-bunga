<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getAll($request->all());
        return $this->successResponse(ProductResource::collection($products)->response()->getData(true), 'Daftar produk');
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->productService->create(
            $request->validated(), 
            $request->file('images', [])
        );
        return $this->successResponse(new ProductResource($product), 'Berhasil membuat produk', 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'images']);
        return $this->successResponse(new ProductResource($product), 'Detail produk');
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $updatedProduct = $this->productService->update(
            $product, 
            $request->validated(), 
            $request->file('images', [])
        );
        return $this->successResponse(new ProductResource($updatedProduct), 'Berhasil mengupdate produk');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);
        return $this->successResponse(null, 'Berhasil menghapus produk');
    }
}
