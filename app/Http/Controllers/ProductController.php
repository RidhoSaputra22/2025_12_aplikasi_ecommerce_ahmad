<?php

namespace App\Http\Controllers;

use App\Livewire\User\Products\Detail;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function produk(Request $request)
    {
        $query = Product::query()
            ->with(['productImages', 'productVariants', 'vendor'])
            ->where('status', 'ACTIVE');

        // Filter: search
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter: category
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category_id', (int) $request->input('category'));
        }

        // Filter: price (supports: price=all | price=10000-50000 | price=100000+ | min_price/max_price)
        if ($request->input('price') !== 'all') {
            $min = $request->input('min_price');
            $max = $request->input('max_price');

            if ($min !== null || $max !== null) {
                if ($min !== null) $query->where('price', '>=', (int) $min);
                if ($max !== null) $query->where('price', '<=', (int) $max);
            } else {
                $price = (string) $request->input('price', '');
                if (preg_match('/^(\d+)\-(\d+)$/', $price, $m)) {
                    $query->whereBetween('price', [(int) $m[1], (int) $m[2]]);
                } elseif (preg_match('/^(\d+)\+$/', $price, $m)) {
                    $query->where('price', '>=', (int) $m[1]);
                }
            }
        }

        // Sort
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = \App\Models\Category::all();



        return view('produk', compact('products', 'categories', 'request'));
    }
}
