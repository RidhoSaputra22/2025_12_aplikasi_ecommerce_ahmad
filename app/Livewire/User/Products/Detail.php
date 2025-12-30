<?php

namespace App\Livewire\User\Products;

use App\Models\Cart;
use App\Models\Product;
use Livewire\Component;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Detail extends Component
{

    public ?string $slug = null;
    public int $quantity = 1;
    public ?int $selectedVariantId = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }



    public function selectVariant(int $variantId): void
    {
        $this->selectedVariantId = $variantId;
        $this->quantity = max(1, $this->quantity);
    }

    public function incrementQuantity(): void
    {
        $current = max(1, (int) $this->quantity);

        if ($this->selectedVariantId) {
            $stock = (int) ProductVariant::query()->where('id', $this->selectedVariantId)->value('stock');
            if ($stock > 0) {
                $this->quantity = min($current + 1, $stock);
                return;
            }
        }

        $this->quantity = $current + 1;
    }

    public function decrementQuantity(): void
    {
        $this->quantity = max(1, (int) $this->quantity - 1);
    }

    public function updatedQuantity($value): void
    {
        $this->quantity = max(1, (int) $value);
    }

    public function addToCart()
    {
        // return sleep(2);
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $product = Product::query()->where('slug', $this->slug)->firstOrFail();

        $variantId = $this->selectedVariantId
            ?? $product->productVariants()->orderBy('id')->value('id');

        if (!$variantId) {
            $this->addError('selectedVariantId', 'Variant tidak tersedia.');
            return;
        }

        /** @var ProductVariant|null $variant */
        $variant = ProductVariant::query()
            ->where('id', $variantId)
            ->where('product_id', $product->id)
            ->first();

        if (!$variant) {
            $this->addError('selectedVariantId', 'Variant tidak valid.');
            return;
        }

        $requestedQty = max(1, (int) $this->quantity);

        DB::transaction(function () use ($requestedQty, $variant) {
            /** @var Cart $cart */
            $cart = Cart::query()->firstOrCreate([
                'user_id' => Auth::id(),
            ]);

            /** @var CartItem|null $item */
            $item = CartItem::query()->where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $currentQty = (int) ($item?->quantity ?? 0);
            $newQty = $currentQty + $requestedQty;

            if ($newQty > (int) $variant->stock) {
                $this->addError('quantity', 'Stok tidak mencukupi untuk jumlah yang dipilih.');
                return;
            }

            CartItem::query()->updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'quantity' => $newQty,
                    'price' => $variant->price,
                ]
            );
        });

        $this->dispatch('cart-updated');



        if (!$this->getErrorBag()->has('quantity')) {
            session()->flash('success', 'Produk berhasil dimasukkan ke keranjang.');
        }
    }




    public function render()
    {
        $product = Product::with(['productImages', 'productVariants', 'reviews.user', 'category'])->where('slug', $this->slug)->firstOrFail();
        $productImages = $product->productImages;
        $productVariants = $product->productVariants;
        $reviews = $product->reviews()->with('user')->get();

        // dd($product, $productImages, $productVariants, $reviews);

        if ($this->selectedVariantId === null) {
            $this->selectedVariantId = $productVariants->first()?->id;
        }

        $selectedVariant = $productVariants->firstWhere('id', $this->selectedVariantId)
            ?? $productVariants->first();

        return view('user.products.detail', compact('product', 'productImages', 'reviews', 'productVariants', 'selectedVariant'))
            ->extends('layouts.app');
    }
}
