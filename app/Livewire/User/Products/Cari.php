<?php

namespace App\Livewire\User\Products;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Models\Product;
use App\Models\Category;

class Cari extends Component
{

    use WithPagination, WithoutUrlPagination;



    public string $search = '';
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
            ->with('category'); // eager load (recommended)

        // Filter kategori
        if ($this->selectedCategorySlug) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->selectedCategorySlug);
            });
        }

        // Filter harga
        if ($this->selectedHarga === 'low_to_high') {
            $query->orderBy('price', 'asc');
        } elseif ($this->selectedHarga === 'high_to_low') {
            $query->orderBy('price', 'desc');
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
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('user.products.cari', [
            'products'   => $this->getProducts(),
            'categories' => Category::all(),
        ])->extends('layouts.app')->section('content');
    }
}
