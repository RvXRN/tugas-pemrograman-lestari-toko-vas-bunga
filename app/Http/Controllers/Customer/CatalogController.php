<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use ApiResponseTrait;

    protected ProductService $productService;
    protected CategoryService $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    public function products(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['is_active'] = true; // Only show active products to public

        $products = $this->productService->getAll($filters);
        return $this->successResponse(ProductResource::collection($products)->response()->getData(true), 'Daftar katalog produk');
    }

    public function showProduct(string $slug): JsonResponse
    {
        $product = $this->productService->getBySlug($slug);
        
        if (!$product->is_active) {
            return $this->errorResponse('Produk tidak ditemukan atau tidak aktif', 404);
        }

        return $this->successResponse(new ProductResource($product), 'Detail produk');
    }

    public function categories(): JsonResponse
    {
        $categories = $this->categoryService->getAll();
        return $this->successResponse(CategoryResource::collection($categories), 'Daftar kategori');
    }
}
