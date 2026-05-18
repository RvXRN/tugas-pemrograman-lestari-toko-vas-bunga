<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();
        return $this->successResponse(CategoryResource::collection($categories), 'Berhasil mengambil daftar kategori');
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());
        return $this->successResponse(new CategoryResource($category), 'Berhasil membuat kategori', 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('children');
        return $this->successResponse(new CategoryResource($category), 'Detail kategori');
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->update($category, $request->validated());
        return $this->successResponse(new CategoryResource($updatedCategory), 'Berhasil mengupdate kategori');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);
        return $this->successResponse(null, 'Berhasil menghapus kategori');
    }
}
