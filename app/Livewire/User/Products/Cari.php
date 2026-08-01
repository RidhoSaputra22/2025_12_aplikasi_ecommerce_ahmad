<?php

namespace App\Livewire\User\Products;

use App\Enums\ProductStatus;
use App\Enums\VendorStatus;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Cari extends Component
{
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public string $vendorSearch = '';

    public ?string $selectedCategorySlug = null;

    public ?string $selectedHarga = null;

    public ?string $selectedSortBy = null;

    public function mount()
    {
        if (request()->has('category')) {
            $this->selectedCategorySlug = request('category');
        }
    }

    /**
     * Auto reset pagination ketika filter berubah
     */
    public function updated($property)
    {
        if (in_array($property, [
            'search',
            'vendorSearch',
            'selectedCategorySlug',
            'selectedHarga',
            'selectedSortBy',
        ])) {
            $this->resetPage();
        }
    }

    /**
     * Query produk (AMAN & TER-GROUPING)
     */
    public function getProducts()
    {
        $query = Product::query()
            ->with(['category', 'productVariants', 'productImages', 'vendor'])
            ->where('status', ProductStatus::Active)
            ->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('status', VendorStatus::Active))
            ->whereHas('productVariants');

        // Filter kategori
        if ($this->selectedCategorySlug) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->selectedCategorySlug);
            });
        }

        // Filter harga
        if ($this->selectedHarga === 'low_to_high') {
            $query->withMin('productVariants', 'price')
                ->orderBy('product_variants_min_price', 'asc');
        } elseif ($this->selectedHarga === 'high_to_low') {
            $query->withMin('productVariants', 'price')
                ->orderBy('product_variants_min_price', 'desc');
        }

        // Sorting
        if ($this->selectedSortBy === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($this->selectedSortBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        }

        // Search (GROUPED)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('vendor', function ($vendorQuery) {
                        $vendorQuery->where('store_name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->vendorSearch) {
            $query->whereHas('vendor', function ($vendorQuery) {
                $vendorQuery->where('store_name', 'like', '%'.$this->vendorSearch.'%');
            });
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('user.products.cari', [
            'products' => $this->getProducts(),
            'categories' => Category::all(),
        ])->extends('layouts.app')->section('content');
    }
}
