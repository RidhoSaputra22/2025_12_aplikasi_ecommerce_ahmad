<?php

namespace App\Livewire\User\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CartSummary extends Component
{

    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public string $province = '';
    public string $city = '';
    public string $district = '';
    public string $postal_code = '';
    public string $address = '';

    protected $listeners = [
        'cart-updated' => '$refresh',
    ];

    public function mount(): void
    {
        if (!Auth::check()) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        $this->name = (string) ($user->name ?? 'TEST');
        $this->email = (string) ($user->email ?? 'TEST');
        $this->phone = (string) ($user->phone ?? 'TEST');

        // $latestAddress = $user->addresses()->latest('id')->first();
        $this->province = (string) ($latestAddress->province ?? 'TEST');
        $this->city = (string) ($latestAddress->city ?? 'TEST');
        $this->district = (string) ($latestAddress->district ?? 'TEST');
        $this->postal_code = (string) ($latestAddress->postal_code ?? 'TEST');
        $this->address = (string) ($latestAddress->address ?? 'TEST');
        // if ($latestAddress) {
        // }
    }

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
            ->with(['productVariant.product'])
            ->where('cart_id', $cartId)
            ->get();
    }

    public function getItemsCountProperty(): int
    {
        return (int) $this->cartItems->sum(fn (CartItem $item) => (int) $item->quantity);
    }

    public function getSubtotalProperty(): float
    {
        return (float) $this->cartItems->sum(fn (CartItem $item) => ((float) $item->price) * ((int) $item->quantity));
    }

    public function checkout(){
        return sleep(2);
    }

    public function checkoutMMXXX(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
        ], [
            'postal_code.required' => 'Kode pos wajib diisi.',
        ]);

        if ($this->cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang masih kosong.'
            ]);
        }

        DB::transaction(function () use ($validated) {
            /** @var User $user */
            $user = Auth::user();

            $user->forceFill(Arr::only($validated, ['name', 'email', 'phone']))->save();

            $cartId = Cart::query()
                ->where('user_id', $user->id)
                ->value('id');

            if (!$cartId) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang tidak ditemukan.'
                ]);
            }

            $cartItems = CartItem::query()
                ->with(['productVariant.product'])
                ->where('cart_id', $cartId)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang masih kosong.'
                ]);
            }

            $totalAmount = (float) $cartItems->sum(fn (CartItem $item) => ((float) $item->price) * ((int) $item->quantity));
            if ($totalAmount <= 0) {
                throw ValidationException::withMessages([
                    'cart' => 'Total tidak valid.'
                ]);
            }

            $shipmentAddress = ShipmentAddress::query()->create([
                'user_id' => $user->id,
                'province' => $validated['province'],
                'city' => $validated['city'],
                'district' => $validated['district'],
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
            ]);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            $vendorsGrouped = $cartItems->groupBy(function (CartItem $item) {
                return (int) ($item->productVariant?->product?->vendor_id ?? 0);
            });

            foreach ($vendorsGrouped as $vendorId => $items) {
                if ((int) $vendorId <= 0) {
                    throw ValidationException::withMessages([
                        'cart' => 'Vendor produk tidak valid.'
                    ]);
                }

                $vendorSubtotal = (float) $items->sum(fn (CartItem $item) => ((float) $item->price) * ((int) $item->quantity));

                $orderVendor = OrderVendor::query()->create([
                    'order_id' => $order->id,
                    'vendor_id' => (int) $vendorId,
                    'subtotal' => $vendorSubtotal,
                    'status' => 'pending',
                ]);

                foreach ($items as $cartItem) {
                    $variant = $cartItem->productVariant;
                    if (!$variant) {
                        throw ValidationException::withMessages([
                            'cart' => 'Variant produk tidak tersedia.'
                        ]);
                    }

                    $qty = (int) $cartItem->quantity;
                    if ($qty <= 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Jumlah produk tidak valid.'
                        ]);
                    }

                    $variantFresh = $variant->newQuery()->whereKey($variant->id)->lockForUpdate()->first();
                    if (!$variantFresh) {
                        throw ValidationException::withMessages([
                            'cart' => 'Variant produk tidak tersedia.'
                        ]);
                    }

                    $stock = (int) $variantFresh->stock;
                    if ($qty > $stock) {
                        throw ValidationException::withMessages([
                            'cart' => 'Stok tidak mencukupi untuk salah satu produk.'
                        ]);
                    }

                    OrderItem::query()->create([
                        'order_vendor_id' => $orderVendor->id,
                        'product_variant_id' => $variantFresh->id,
                        'price' => (float) $cartItem->price,
                        'quantity' => $qty,
                        'total' => ((float) $cartItem->price) * $qty,
                    ]);

                    $variantFresh->update([
                        'stock' => $stock - $qty,
                    ]);
                }

                Shipment::query()->create([
                    'order_vendor_id' => $orderVendor->id,
                    'shipment_address_id' => $shipmentAddress->id,
                    'shipment_courier_id' => null,
                    'tracking_number' => null,
                    'shipping_cost' => 0,
                    'status' => 'pending',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'payment_method' => 'manual',
                'payment_gateway' => null,
                'amount' => $totalAmount,
                'status' => 'pending',
                'transaction_reference' => null,
                'paid_at' => null,
            ]);

            CartItem::query()->where('cart_id', $cartId)->delete();
        });

        session()->flash('success', 'Checkout berhasil. Pesanan dibuat.');
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('user.cart.cart-summary');
    }
}
