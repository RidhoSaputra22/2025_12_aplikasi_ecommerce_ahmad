<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductFormPage extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    // Product fields
    public string $name = '';

    public ?int $category_id = null;

    public ?string $description = null;

    public float $price = 0;

    public float $weight = 0;

    public string $status = 'draft';

    // Variants
    public array $variants = [];

    // Images
    public array $existingImages = [];

    public $newImages = [];

    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;

        if ($productId) {
            $vendor = Auth::user()?->vendor;
            if (! $vendor) {
                return;
            }

            $product = Product::with(['productVariants', 'productImages'])
                ->where('id', $productId)
                ->where('vendor_id', $vendor->id)
                ->first();

            if (! $product) {
                session()->flash('error', 'Produk tidak ditemukan.');

                return;
            }

            $this->name = $product->name;
            $this->category_id = $product->category_id;
            $this->description = $product->description;
            $this->price = (float) $product->price;
            $this->weight = (float) $product->weight;
            $this->status = $product->status->value;

            $this->existingImages = $product->productImages->map(fn ($img) => [
                'id' => $img->id,
                'image' => $img->image,
            ])->toArray();

            $this->variants = $product->productVariants->map(fn ($v) => [
                'id' => $v->id,
                'variant_name' => $v->variant_name,
                'sku' => $v->sku,
                'price' => (float) $v->price,
                'stock' => (int) $v->stock,
            ])->toArray();
        }
    }

    public function addVariant(): void
    {
        $this->variants[] = [
            'id' => null,
            'variant_name' => '',
            'sku' => '',
            'price' => 0,
            'stock' => 0,
        ];
    }

    public function removeVariant(int $index): void
    {
        $variant = $this->variants[$index] ?? null;

        if ($variant && $variant['id']) {
            $vendorId = Auth::user()?->vendor?->id;
            $variantModel = ProductVariant::query()
                ->whereKey($variant['id'])
                ->whereHas('product', fn ($query) => $query->where('vendor_id', $vendorId))
                ->first();

            if (! $variantModel) {
                return;
            }

            if ($variantModel->orderItems()->exists()) {
                $this->addError("variants.{$index}.variant_name", 'Varian yang sudah pernah dipesan tidak dapat dihapus.');

                return;
            }

            $variantModel->delete();
        }

        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function removeExistingImage(int $index): void
    {
        $image = $this->existingImages[$index] ?? null;
        if ($image) {
            $vendorId = Auth::user()?->vendor?->id;
            $imageModel = ProductImage::query()
                ->whereKey($image['id'])
                ->whereHas('product', fn ($query) => $query->where('vendor_id', $vendorId))
                ->first();

            if (! $imageModel) {
                return;
            }

            $imageModel->delete();
            Storage::disk('public')->delete($imageModel->image);
        }

        unset($this->existingImages[$index]);
        $this->existingImages = array_values($this->existingImages);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'weight' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.variant_name' => ['required', 'string', 'max:100'],
            'variants.*.sku' => ['required', 'string', 'max:50', 'distinct'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'newImages.*' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'price.required' => 'Harga wajib diisi.',
            'weight.required' => 'Berat wajib diisi.',
            'variants.*.variant_name.required' => 'Nama varian wajib diisi.',
            'variants.required' => 'Minimal satu varian wajib ditambahkan.',
            'variants.min' => 'Minimal satu varian wajib ditambahkan.',
            'variants.*.sku.required' => 'SKU varian wajib diisi.',
            'variants.*.sku.distinct' => 'SKU setiap varian harus berbeda.',
            'variants.*.price.required' => 'Harga varian wajib diisi.',
            'variants.*.stock.required' => 'Stok varian wajib diisi.',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $vendor = Auth::user()?->vendor;
        if (! $vendor) {
            session()->flash('error', 'Vendor tidak ditemukan.');

            return;
        }

        foreach ($validated['variants'] as $index => $variantData) {
            $skuExists = ProductVariant::query()
                ->where('sku', $variantData['sku'])
                ->when(
                    ! empty($variantData['id']),
                    fn ($query) => $query->whereKeyNot($variantData['id']),
                )
                ->exists();

            if ($skuExists) {
                throw ValidationException::withMessages([
                    "variants.{$index}.sku" => 'SKU sudah digunakan oleh varian lain.',
                ]);
            }
        }

        $product = DB::transaction(function () use ($vendor): Product {
            if ($this->productId) {
                $product = Product::query()
                    ->whereKey($this->productId)
                    ->where('vendor_id', $vendor->id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'name' => 'Produk tidak ditemukan.',
                    ]);
                }

                $product->update([
                    'name' => $this->name,
                    'category_id' => $this->category_id,
                    'description' => $this->description,
                    'price' => $this->price,
                    'weight' => $this->weight,
                    'status' => $this->status,
                ]);
            } else {
                $product = Product::query()->create([
                    'vendor_id' => $vendor->id,
                    'name' => $this->name,
                    'category_id' => $this->category_id,
                    'description' => $this->description,
                    'price' => $this->price,
                    'weight' => $this->weight,
                    'status' => $this->status,
                ]);

            }

            foreach ($this->variants as $index => $variantData) {
                if (! empty($variantData['id'])) {
                    $updated = ProductVariant::query()
                        ->whereKey($variantData['id'])
                        ->where('product_id', $product->id)
                        ->update([
                            'variant_name' => $variantData['variant_name'],
                            'sku' => $variantData['sku'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                        ]);

                    if ($updated !== 1) {
                        throw ValidationException::withMessages([
                            "variants.{$index}.variant_name" => 'Varian tidak valid untuk produk ini.',
                        ]);
                    }
                } else {
                    ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'variant_name' => $variantData['variant_name'],
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                    ]);
                }
            }

            return $product;
        });
        $this->productId = $product->id;

        // Upload new images
        if ($this->newImages) {
            $storedPaths = [];

            try {
                DB::transaction(function () use ($product, &$storedPaths): void {
                    foreach ($this->newImages as $image) {
                        $path = $image->store('products/images', 'public');
                        $storedPaths[] = $path;

                        ProductImage::query()->create([
                            'product_id' => $product->id,
                            'image' => $path,
                            'is_primary' => false,
                        ]);
                    }
                });
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($storedPaths);
                Log::error('Gagal menyimpan gambar produk.', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
                session()->flash('error', 'Produk tersimpan, tetapi gambar gagal diunggah. Silakan coba lagi.');

                return;
            }

            $this->newImages = [];
        }

        // Refresh existing images
        $this->existingImages = $product->fresh()->productImages->map(fn ($img) => [
            'id' => $img->id,
            'image' => $img->image,
        ])->toArray();

        // Refresh variants
        $this->variants = $product->fresh()->productVariants->map(fn ($v) => [
            'id' => $v->id,
            'variant_name' => $v->variant_name,
            'sku' => $v->sku,
            'price' => (float) $v->price,
            'stock' => (int) $v->stock,
        ])->toArray();

        session()->flash('success', 'Produk berhasil disimpan.');
    }

    public function cancel(): void
    {
        $this->redirectRoute('vendor.dashboard', ['tab' => 'products']);
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        return view('vendor.dashboard.product-form-page', [
            'categories' => $categories,
        ]);
    }
}
