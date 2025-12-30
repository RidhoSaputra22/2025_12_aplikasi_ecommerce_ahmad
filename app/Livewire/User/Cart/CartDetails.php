<?php

namespace App\Livewire\User\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CartDetails extends Component
{

    protected $listeners = [
        'cart-updated' => '$refresh',
    ];

    public function getCartItemsProperty(): Collection
    {
        if (!Auth::check()) {
            return collect();
        }

        $cartId = Cart::query()
            ->where('user_id', Auth::id())
            ->value('id');

        if (!$cartId) {
            return collect();
        }

        return CartItem::query()
            ->with([
                'productVariant.product.productImages' => fn ($query) => $query->orderByDesc('is_primary'),
            ])
            ->where('cart_id', $cartId)
            ->orderByDesc('id')
            ->get();
    }

    public function getSubtotalProperty(): float
    {
        return (float) $this->cartItems->sum(function (CartItem $item) {
            return ((float) $item->price) * ((int) $item->quantity);
        });
    }

    public function increment(int $cartItemId): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        DB::transaction(function () use ($cartItemId) {
            /** @var CartItem $item */
            $item = CartItem::query()
                ->whereKey($cartItemId)
                ->whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
                ->lockForUpdate()
                ->firstOrFail();

            /** @var ProductVariant|null $variant */
            $variant = ProductVariant::query()
                ->whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                $this->addError('quantity', 'Variant tidak tersedia.');
                $item->delete();
                return;
            }

            $stock = (int) $variant->stock;
            $currentQty = (int) $item->quantity;
            $newQty = $currentQty + 1;

            if ($newQty > $stock) {
                $this->addError('quantity', 'Stok tidak mencukupi.');
                return;
            }

            $item->update(['quantity' => $newQty]);
        });

        $this->dispatch('cart-updated');
    }

    public function decrement(int $cartItemId): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        DB::transaction(function () use ($cartItemId) {
            /** @var CartItem $item */
            $item = CartItem::query()
                ->whereKey($cartItemId)
                ->whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
                ->lockForUpdate()
                ->firstOrFail();

            $currentQty = (int) $item->quantity;
            $newQty = max(1, $currentQty - 1);

            if ($newQty !== $currentQty) {
                $item->update(['quantity' => $newQty]);
            }
        });

        $this->dispatch('cart-updated');
    }

    public function setQuantity(int $cartItemId, mixed $quantity): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $qty = (int) $quantity;

        DB::transaction(function () use ($cartItemId, $qty) {
            /** @var CartItem $item */
            $item = CartItem::query()
                ->whereKey($cartItemId)
                ->whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
                ->lockForUpdate()
                ->firstOrFail();

            if ($qty <= 0) {
                $item->delete();
                return;
            }

            /** @var ProductVariant|null $variant */
            $variant = ProductVariant::query()
                ->whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                $this->addError('quantity', 'Variant tidak tersedia.');
                $item->delete();
                return;
            }

            $stock = (int) $variant->stock;

            if ($qty > $stock) {
                $this->addError('quantity', 'Stok tidak mencukupi.');

                if ($stock <= 0) {
                    $item->delete();
                    return;
                }

                $item->update(['quantity' => $stock]);
                return;
            }

            $item->update(['quantity' => max(1, $qty)]);
        });

        $this->dispatch('cart-updated');
    }

    public function removeItem(int $cartItemId): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        CartItem::query()
            ->whereKey($cartItemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
            ->delete();

        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $cartId = Cart::query()
            ->where('user_id', Auth::id())
            ->value('id');

        if ($cartId) {
            CartItem::query()->where('cart_id', $cartId)->delete();
        }

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('user.cart.cart-details');
    }
}
