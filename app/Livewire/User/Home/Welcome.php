<?php

namespace App\Livewire\User\Home;

use App\Enums\ProductStatus;
use App\Enums\VendorStatus;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class Welcome extends Component
{
    public int $selectedCategoryId = 1;

    public bool $readyToLoad = false;

    public function loadInitialData(): void
    {
        $this->readyToLoad = true;

        if ($this->selectedCategoryId > 0 && Category::whereKey($this->selectedCategoryId)->exists()) {
            return;
        }

        $this->selectedCategoryId = (int) (Category::query()->value('id') ?? 0);
    }

    public function selectCategory(string $categoryName)
    {
        $this->readyToLoad = true;

        $categoryId = Category::query()
            ->where('name', $categoryName)
            ->value('id');

        if ($categoryId) {
            $this->selectedCategoryId = (int) $categoryId;
        }
    }

    public function render()
    {
        if (! $this->readyToLoad) {
            $categories = collect();
            $products = collect();
            $produkUnggulan = collect();

            return view('user.home.welcome', compact('categories', 'products', 'produkUnggulan'))
                ->extends('layouts.app')
                ->section('content');
        }

        $activeProducts = fn ($query) => $query
            ->where('status', ProductStatus::Active)
            ->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('status', VendorStatus::Active))
            ->whereHas('productVariants');

        $categories = Category::query()
            ->withCount(['products' => $activeProducts])
            ->take(5)
            ->get();

        $products = $this->selectedCategoryId > 0
            ? Product::query()
                ->with(['category', 'vendor', 'productImages'])
                ->where('category_id', $this->selectedCategoryId)
                ->where($activeProducts)
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $produkUnggulan = Product::query()
            ->with(['category', 'vendor', 'productImages'])
            ->where('price', '>=', 400000)
            ->where($activeProducts)
            ->latest()
            ->take(5)
            ->get();
        $selectedCategoryName = Category::where('id', $this->selectedCategoryId)->value('name') ?? '';

        return view('user.home.welcome', compact('categories', 'products', 'produkUnggulan', 'selectedCategoryName'))->extends('layouts.app')->section('content');
    }
}
