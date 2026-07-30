<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ProductPage extends Component
{
    use WithPagination;

    public ?string $selectedStatus = null;

    public ?string $search = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function createProduct(): void
    {
        $this->redirectRoute('vendor.dashboard', ['tab' => 'product-form']);
    }

    public function editProduct(int $productId): void
    {
        $this->redirectRoute('vendor.dashboard', ['tab' => 'product-form', 'product_id' => $productId]);
    }

    public function deleteProduct(int $productId): void
    {
        $vendor = Auth::user()?->vendor;
        if (! $vendor) {
            return;
        }

        $product = Product::where('id', $productId)->where('vendor_id', $vendor->id)->first();
        if (! $product) {
            session()->flash('error', 'Produk tidak ditemukan.');

            return;
        }

        if ($product->productVariants()->whereHas('orderItems')->exists()) {
            $product->update(['status' => ProductStatus::Archived]);
            session()->flash('error', 'Produk memiliki riwayat pesanan sehingga diarsipkan dan tidak dihapus.');

            return;
        }

        // Delete images
        foreach ($product->productImages as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $product->productImages()->delete();
        $product->productVariants()->delete();
        $product->delete();

        session()->flash('success', 'Produk berhasil dihapus.');
    }

    public function toggleStatus(int $productId): void
    {
        $vendor = Auth::user()?->vendor;
        if (! $vendor) {
            return;
        }

        $product = Product::where('id', $productId)->where('vendor_id', $vendor->id)->first();
        if (! $product) {
            return;
        }

        $newStatus = $product->status === ProductStatus::Active ? ProductStatus::Draft : ProductStatus::Active;
        $product->update(['status' => $newStatus]);

        session()->flash('success', 'Status produk berhasil diubah.');
    }

    public function render()
    {
        $vendor = Auth::user()?->vendor;

        if (! $vendor) {
            return view('vendor.dashboard.product-page', [
                'products' => collect(),
                'statusOptions' => [],
            ]);
        }

        $products = Product::with(['productImages', 'productVariants', 'category'])
            ->where('vendor_id', $vendor->id)
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->latest('created_at')
            ->paginate(10);

        $statusOptions = array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], ProductStatus::cases());

        return view('vendor.dashboard.product-page', [
            'products' => $products,
            'statusOptions' => $statusOptions,
        ]);
    }
}
