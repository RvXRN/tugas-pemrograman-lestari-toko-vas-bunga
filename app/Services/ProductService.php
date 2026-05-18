<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function getAll(array $filters = [])
    {
        $query = Product::with(['category', 'images']);

        if (!empty($filters['search'])) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                // ILIKE with pg_trgm index is highly optimized
                $query->where('name', 'ILIKE', '%' . $filters['search'] . '%');
            } else {
                // Fallback for SQLite testing
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($filters['search']) . '%']);
            }
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate(15);
    }

    public function getBySlug(string $slug): Product
    {
        return Product::with(['category', 'images'])->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data, ?array $images = []): Product
    {
        return DB::transaction(function () use ($data, $images) {
            $data['slug'] = Str::slug($data['name']);
            
            $originalSlug = $data['slug'];
            $count = 1;
            while (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $count++;
            }

            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            $product = Product::create($data);

            if (!empty($images)) {
                $this->handleImagesUpload($product, $images);
            }

            return $product->load('images');
        });
    }

    public function update(Product $product, array $data, ?array $images = []): Product
    {
        return DB::transaction(function () use ($product, $data, $images) {
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = Str::slug($data['name']);
                
                $originalSlug = $data['slug'];
                $count = 1;
                while (Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists()) {
                    $data['slug'] = $originalSlug . '-' . $count++;
                }
            }

            $product->update($data);

            if (!empty($images)) {
                // Delete old images
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
                
                $this->handleImagesUpload($product, $images);
            }

            return $product->load('images');
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            $product->delete();
        });
    }

    protected function handleImagesUpload(Product $product, array $images): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSizeBytes = 2 * 1024 * 1024; // 2MB

        $isPrimary = true;
        foreach ($images as $image) {
            if (!($image instanceof UploadedFile)) {
                continue;
            }

            // Validate file size
            if ($image->getSize() > $maxSizeBytes) {
                throw new \InvalidArgumentException("Ukuran gambar tidak boleh melebihi 2MB.");
            }

            // Validate MIME type using finfo (reads magic bytes, not just extension)
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($image->getRealPath());

            if (!in_array($realMime, $allowedMimes, true)) {
                throw new \InvalidArgumentException("Tipe file tidak diizinkan: {$realMime}. Hanya JPEG, PNG, WebP, dan GIF yang diterima.");
            }

            // Use UUID-based filename to prevent path traversal and filename guessing
            $extension = $image->extension();
            $secureFilename = \Illuminate\Support\Str::uuid()->toString() . '.' . $extension;
            $path = $image->storeAs('products', $secureFilename, 'public');

            $product->images()->create([
                'image_path' => $path,
                'is_primary'  => $isPrimary,
            ]);
            $isPrimary = false;
        }
    }
}
