<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\OrderVendorStatus;
use App\Enums\ShipmentStatus;
use App\Models\OrderVendor;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetailPage extends Component
{
    public ?int $orderId = null;

    public ?string $tracking_number = null;

    public function mount(?int $orderId = null): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->orderId = $orderId;
    }

    public function getOrderVendorProperty(): ?OrderVendor
    {
        if (!$this->orderId || !Auth::check()) {
            return null;
        }

        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return null;
        }

        return OrderVendor::with([
            'order.user',
            'order.payment',
            'orderItems.productVariant.product.productImages',
            'orderItems.productVariant.product.category',
            'shipment.shipmentAddress',
            'shipment.shipmentCourier',
        ])
            ->where('id', $this->orderId)
            ->where('vendor_id', $vendor->id)
            ->first();
    }

    public function processOrder(): void
    {
        $orderVendor = $this->orderVendor;
        if (!$orderVendor || $orderVendor->status !== OrderVendorStatus::Pending) {
            session()->flash('error', 'Pesanan tidak bisa diproses.');
            return;
        }

        $orderVendor->update(['status' => OrderVendorStatus::Processed]);

        // Notify customer
        $this->notifyCustomer($orderVendor, 'Pesanan Diproses', 'Pesanan Anda sedang diproses oleh vendor.');

        session()->flash('success', 'Pesanan berhasil diproses.');
    }

    public function shipOrder(): void
    {
        $orderVendor = $this->orderVendor;
        if (!$orderVendor || $orderVendor->status !== OrderVendorStatus::Processed) {
            session()->flash('error', 'Pesanan tidak bisa dikirim.');
            return;
        }

        $this->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
        ], [
            'tracking_number.required' => 'Nomor resi wajib diisi.',
        ]);

        $orderVendor->update(['status' => OrderVendorStatus::Shipped]);

        if ($orderVendor->shipment) {
            $orderVendor->shipment->update([
                'tracking_number' => $this->tracking_number,
                'status' => ShipmentStatus::Shipped,
                'shipped_at' => now(),
            ]);
        }

        // Check if all vendor orders shipped → update main order
        $this->updateMainOrderStatus($orderVendor);

        // Notify customer
        $this->notifyCustomer($orderVendor, 'Pesanan Dikirim', 'Pesanan Anda telah dikirim. No Resi: ' . $this->tracking_number);

        session()->flash('success', 'Pesanan berhasil dikirim dengan resi: ' . $this->tracking_number);
    }

    public function completeOrder(): void
    {
        $orderVendor = $this->orderVendor;
        if (!$orderVendor || $orderVendor->status !== OrderVendorStatus::Shipped) {
            session()->flash('error', 'Pesanan tidak bisa diselesaikan.');
            return;
        }

        $orderVendor->update(['status' => OrderVendorStatus::Completed]);

        if ($orderVendor->shipment) {
            $orderVendor->shipment->update([
                'status' => ShipmentStatus::Delivered,
                'delivered_at' => now(),
            ]);
        }

        // Credit vendor wallet
        $this->creditVendorWallet($orderVendor);

        // Check if all vendor orders completed → update main order
        $this->updateMainOrderStatus($orderVendor);

        // Notify customer
        $this->notifyCustomer($orderVendor, 'Pesanan Selesai', 'Pesanan Anda telah selesai dan diterima.');

        session()->flash('success', 'Pesanan telah diselesaikan.');
    }

    protected function updateMainOrderStatus(OrderVendor $orderVendor): void
    {
        $order = $orderVendor->order;
        if (!$order) {
            return;
        }

        $allVendorOrders = $order->orderVendors;

        // If all shipped
        if ($allVendorOrders->every(fn($ov) => in_array($ov->status, [OrderVendorStatus::Shipped, OrderVendorStatus::Completed]))) {
            $order->update(['status' => 'shipped']);
        }

        // If all completed
        if ($allVendorOrders->every(fn($ov) => $ov->status === OrderVendorStatus::Completed)) {
            $order->update(['status' => 'completed']);
        }
    }

    protected function creditVendorWallet(OrderVendor $orderVendor): void
    {
        $vendor = $orderVendor->vendor;
        if (!$vendor) {
            return;
        }

        $wallet = $vendor->vendorWallet;
        if (!$wallet) {
            $wallet = $vendor->vendorWallet()->create(['balance' => 0]);
        }

        $amount = (float) $orderVendor->subtotal;
        $wallet->increment('balance', $amount);

        $wallet->transactions()->create([
            'vendor_wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => 'Pendapatan dari pesanan #' . $orderVendor->order?->order_number,
            'reference_id' => $orderVendor->id,
        ]);
    }

    protected function notifyCustomer(OrderVendor $orderVendor, string $title, string $message): void
    {
        $customer = $orderVendor->order?->user;
        if (!$customer) {
            return;
        }

        $customer->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\OrderStatusUpdated',
            'data' => json_encode([
                'title' => $title,
                'message' => $message,
                'order_id' => $orderVendor->order_id,
                'order_number' => $orderVendor->order?->order_number,
            ]),
        ]);
    }

    public function render()
    {
        return view('vendor.dashboard.order-detail-page');
    }
}
