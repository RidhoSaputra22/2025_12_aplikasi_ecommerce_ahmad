<?php

namespace App\Livewire\User\Cart;

use App\Enums\ProductStatus;
use App\Enums\VendorStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Services\AdminFeeService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CartSummary extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public ?int $shipmentAddressId = null;

    public ?int $redirectOrderId = null;

    /**
     * Pilihan kurir per vendor: [vendor_id => courier_id]
     */
    public array $selectedCouriers = [];

    protected $listeners = [
        'cart-updated' => '$refresh',
    ];

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

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
        }
    }

    public function getSelectedShipmentAddressProperty(): ?ShipmentAddress
    {
        if (! Auth::check()) {
            return null;
        }

        return ShipmentAddress::query()
            ->where('user_id', Auth::id())
            ->first();
    }

    public function getCartItemsProperty(): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        $cartId = Cart::query()
            ->where('user_id', Auth::id())
            ->value('id');

        if (! $cartId) {
            return collect();
        }

        return CartItem::query()
            ->with(['productVariant.product.vendor'])
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

    /**
     * Cart items grouped by vendor_id.
     */
    public function getVendorGroupsProperty(): Collection
    {
        return $this->cartItems->groupBy(function (CartItem $item) {
            return (int) ($item->productVariant?->product?->vendor_id ?? 0);
        });
    }

    /**
     * List semua kurir yang tersedia.
     */
    public function getCouriersProperty(): Collection
    {
        return ShipmentCourier::query()->orderBy('name')->orderBy('service')->get();
    }

    /**
     * Total ongkir berdasarkan kurir yang dipilih per vendor.
     */
    public function getShippingCostProperty(): float
    {
        if (empty($this->selectedCouriers)) {
            return 0;
        }

        $courierIds = array_filter(array_values($this->selectedCouriers));

        if (empty($courierIds)) {
            return 0;
        }

        $couriers = ShipmentCourier::query()->whereIn('id', $courierIds)->get()->keyBy('id');

        $total = 0;
        foreach ($this->selectedCouriers as $vendorId => $courierId) {
            if ($courierId && $couriers->has($courierId)) {
                $total += (float) $couriers->get($courierId)->price;
            }
        }

        return $total;
    }

    /**
     * Grand total = subtotal produk + total ongkir.
     */
    public function getGrandTotalProperty(): float
    {
        return $this->subtotal + $this->shippingCost;
    }

    /**
     * Cek apakah semua vendor sudah dipilih kurirnya.
     */
    public function getAllCouriersSelectedProperty(): bool
    {
        if ($this->cartItems->isEmpty()) {
            return false;
        }

        foreach ($this->vendorGroups as $vendorId => $items) {
            if (empty($this->selectedCouriers[$vendorId])) {
                return false;
            }
        }

        return true;
    }

    public function checkout(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
            'phone' => ['required', 'string', 'max:50'],
            'selectedCouriers' => ['required', 'array'],
            'selectedCouriers.*' => ['required', 'integer', 'exists:shipment_couriers,id'],
        ]);

        // Auto-use the user's single shipment address
        $userAddress = ShipmentAddress::query()
            ->where('user_id', Auth::id())
            ->first();

        if (! $userAddress) {
            throw ValidationException::withMessages([
                'cart' => 'Harap lengkapi alamat pengiriman di halaman profil terlebih dahulu.',
            ]);
        }

        $this->shipmentAddressId = $userAddress->id;

        if ($this->cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang masih kosong.',
            ]);
        }

        // Validasi bahwa semua vendor sudah dipilih kurirnya
        $vendorGroups = $this->cartItems->groupBy(function (CartItem $item) {
            return (int) ($item->productVariant?->product?->vendor_id ?? 0);
        });

        foreach ($vendorGroups as $vendorId => $items) {
            if (empty($this->selectedCouriers[$vendorId])) {
                throw ValidationException::withMessages([
                    'selectedCouriers' => 'Silakan pilih kurir pengiriman untuk semua vendor.',
                ]);
            }
        }

        // Kurir akan dibaca ulang dan dikunci di dalam transaksi.
        $courierIds = array_filter(array_values($this->selectedCouriers));

        DB::transaction(function () use ($validated, $courierIds) {
            /** @var User $user */
            $user = Auth::user();

            $user->forceFill(Arr::only($validated, ['name', 'email', 'phone']))->save();

            $shipmentAddress = ShipmentAddress::query()
                ->whereKey($this->shipmentAddressId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            if (! $shipmentAddress) {
                throw ValidationException::withMessages([
                    'cart' => 'Alamat pengiriman sudah tidak tersedia.',
                ]);
            }

            $couriersMap = ShipmentCourier::query()
                ->whereIn('id', $courierIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $cartId = Cart::query()
                ->where('user_id', $user->id)
                ->value('id');

            if (! $cartId) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang tidak ditemukan.',
                ]);
            }

            $cartItems = CartItem::query()
                ->with(['productVariant.product'])
                ->where('cart_id', $cartId)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang masih kosong.',
                ]);
            }

            // Hitung subtotal produk
            $productSubtotal = (float) $cartItems->sum(fn (CartItem $item) => ((float) $item->price) * ((int) $item->quantity));
            if ($productSubtotal <= 0) {
                throw ValidationException::withMessages([
                    'cart' => 'Total tidak valid.',
                ]);
            }

            // Hitung total ongkir
            $totalShippingCost = 0;
            $vendorsGrouped = $cartItems->groupBy(function (CartItem $item) {
                return (int) ($item->productVariant?->product?->vendor_id ?? 0);
            });

            foreach ($vendorsGrouped as $vendorId => $items) {
                $courierId = $this->selectedCouriers[$vendorId] ?? null;
                $courier = $courierId ? $couriersMap->get($courierId) : null;
                if (! $courier) {
                    throw ValidationException::withMessages([
                        'selectedCouriers' => 'Kurir yang dipilih sudah tidak tersedia.',
                    ]);
                }

                $totalShippingCost += (float) $courier->price;
            }

            $totalAmount = $productSubtotal + $totalShippingCost;

            $order = Order::query()->create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            $adminFeeService = app(AdminFeeService::class);

            foreach ($vendorsGrouped as $vendorId => $items) {
                if ((int) $vendorId <= 0) {
                    throw ValidationException::withMessages([
                        'cart' => 'Vendor produk tidak valid.',
                    ]);
                }

                $vendorSubtotal = (float) $items->sum(fn (CartItem $item) => ((float) $item->price) * ((int) $item->quantity));

                $orderVendor = OrderVendor::query()->create([
                    'order_id' => $order->id,
                    'vendor_id' => (int) $vendorId,
                    'subtotal' => $vendorSubtotal,
                    'status' => 'pending',
                ]);

                $adminFeeService->syncOrderVendor($orderVendor);

                foreach ($items as $cartItem) {
                    $variant = $cartItem->productVariant;
                    if (! $variant) {
                        throw ValidationException::withMessages([
                            'cart' => 'Variant produk tidak tersedia.',
                        ]);
                    }

                    $qty = (int) $cartItem->quantity;
                    if ($qty <= 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Jumlah produk tidak valid.',
                        ]);
                    }

                    $variantFresh = $variant->newQuery()
                        ->whereKey($variant->id)
                        ->whereHas('product', function ($query) {
                            $query
                                ->where('status', ProductStatus::Active)
                                ->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('status', VendorStatus::Active));
                        })
                        ->lockForUpdate()
                        ->first();
                    if (! $variantFresh) {
                        throw ValidationException::withMessages([
                            'cart' => 'Variant produk tidak tersedia.',
                        ]);
                    }

                    $stock = (int) $variantFresh->stock;
                    if ($qty > $stock) {
                        throw ValidationException::withMessages([
                            'cart' => 'Stok tidak mencukupi untuk salah satu produk.',
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

                // Simpan shipment dengan kurir yang dipilih
                $courierId = $this->selectedCouriers[$vendorId] ?? null;
                $courier = $courierId ? $couriersMap->get($courierId) : null;
                if (! $courier) {
                    throw ValidationException::withMessages([
                        'selectedCouriers' => 'Kurir yang dipilih sudah tidak tersedia.',
                    ]);
                }

                $shippingCost = (float) $courier->price;

                Shipment::query()->create([
                    'order_vendor_id' => $orderVendor->id,
                    'shipment_address_id' => $shipmentAddress->id,
                    'shipment_courier_id' => $courierId,
                    'tracking_number' => null,
                    'shipping_cost' => $shippingCost,
                    'status' => 'pending',
                    'shipped_at' => null,
                    'delivered_at' => null,
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'payment_method' => 'midtrans',
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
