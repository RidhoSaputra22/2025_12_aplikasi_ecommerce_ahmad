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
use Livewire\Attributes\On;
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

    public ?int $shipmentAddressId = null;
    public ?int $redirectOrderId = null;

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

        $latestAddress = $user->addresses()->latest('id')->first();

        if ($latestAddress) {
            $this->shipmentAddressId = (int) $latestAddress->id;
            $this->province = (string) ($latestAddress->province ?? '');
            $this->city = (string) ($latestAddress->city ?? '');
            $this->district = (string) ($latestAddress->district ?? '');
            $this->postal_code = (string) ($latestAddress->postal_code ?? '');
            $this->address = (string) ($latestAddress->address ?? '');
        }
    }

    public function getSelectedShipmentAddressProperty(): ?ShipmentAddress
    {
        if (!Auth::check() || !$this->shipmentAddressId) {
            return null;
        }

        return ShipmentAddress::query()
            ->where('id', $this->shipmentAddressId)
            ->where('user_id', Auth::id())
            ->first();
    }

    public function openShippingAddressModal(): void
    {
        $this->dispatch(
            'openModal',
            component: 'user.cart.shipping-address-picker',
            arguments: ['selectedId' => $this->shipmentAddressId],
            title: 'Pilih Alamat Pengiriman',
            maxWidth: '7xl',
        );
    }

    #[On('shipping-address:selected')]
    public function setShippingAddress(int $shipmentAddressId): void
    {
        if (!Auth::check()) {
            return;
        }

        $address = ShipmentAddress::query()
            ->where('id', $shipmentAddressId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return;
        }

        $this->shipmentAddressId = (int) $address->id;
        $this->province = (string) ($address->province ?? '');
        $this->city = (string) ($address->city ?? '');
        $this->district = (string) ($address->district ?? '');
        $this->postal_code = (string) ($address->postal_code ?? '');
        $this->address = (string) ($address->address ?? '');
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



    public function checkout(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'shipmentAddressId' => ['required', 'integer', 'exists:shipment_addresses,id'],
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
                    'shipment_address_id' => $this->shipmentAddressId,
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

            // Simpan order ID untuk redirect ke halaman pembayaran
            $this->redirectOrderId = $order->id;
        });

        session()->flash('success', 'Checkout berhasil. Silakan lakukan pembayaran.');
        $this->dispatch('cart-updated');

        // Redirect ke halaman pembayaran
        if (isset($this->redirectOrderId)) {
            $this->redirectRoute('payment.page', ['orderId' => $this->redirectOrderId]);
        }
    }

    public function render()
    {
        return view('user.cart.cart-summary');
    }
}
