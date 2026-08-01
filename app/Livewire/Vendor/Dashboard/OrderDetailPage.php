<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Models\OrderVendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class OrderDetailPage extends Component
{
    public ?int $orderId = null;

    public ?string $tracking_number = null;

    public function mount(?int $orderId = null): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        $this->orderId = $orderId;
    }

    public function getOrderVendorProperty(): ?OrderVendor
    {
        if (! $this->orderId || ! Auth::check()) {
            return null;
        }

        $vendor = Auth::user()?->vendor;
        if (! $vendor) {
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
        if (
            ! $orderVendor
            || $orderVendor->status !== OrderVendorStatus::Pending
            || ! $orderVendor->order?->hasConfirmedPayment()
        ) {
            session()->flash('error', 'Pesanan tidak bisa diproses.');

            return;
        }

        $updated = OrderVendor::query()
            ->whereKey($orderVendor->id)
            ->where('status', OrderVendorStatus::Pending)
            ->where(function ($query) {
                $query
                    ->whereHas('order', fn ($orderQuery) => $orderQuery->where('payment_status', OrderPaymentStatus::Paid))
                    ->orWhereHas('order.payment', fn ($paymentQuery) => $paymentQuery->where('status', PaymentStatus::Success));
            })
            ->update(['status' => OrderVendorStatus::Processed]);

        if ($updated !== 1) {
            session()->flash('error', 'Status pesanan sudah berubah. Muat ulang halaman.');

            return;
        }

        $orderVendor->refresh();

        // Notify customer
        $this->notifyCustomer($orderVendor, 'Pesanan Diproses', 'Pesanan Anda sedang diproses oleh vendor.');

        session()->flash('success', 'Pesanan berhasil diproses.');
    }

    public function shipOrder(): void
    {
        $orderVendor = $this->orderVendor;
        if (! $orderVendor || $orderVendor->status !== OrderVendorStatus::Processed) {
            session()->flash('error', 'Pesanan tidak bisa dikirim.');

            return;
        }

        $this->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
        ], [
            'tracking_number.required' => 'Nomor resi wajib diisi.',
        ]);

        $shipped = DB::transaction(function () use ($orderVendor): bool {
            $lockedOrderVendor = OrderVendor::query()
                ->whereKey($orderVendor->id)
                ->where('status', OrderVendorStatus::Processed)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrderVendor) {
                return false;
            }

            $shipment = $lockedOrderVendor->shipment()->lockForUpdate()->first();
            if (! $shipment) {
                return false;
            }

            $lockedOrderVendor->update(['status' => OrderVendorStatus::Shipped]);
            $shipment->update([
                'tracking_number' => $this->tracking_number,
                'status' => ShipmentStatus::Shipped,
                'shipped_at' => now(),
            ]);

            return true;
        });

        if (! $shipped) {
            session()->flash('error', 'Data pengiriman tidak tersedia atau status pesanan sudah berubah.');

            return;
        }

        $orderVendor->refresh();

        // Check if all vendor orders shipped → update main order
        $this->updateMainOrderStatus($orderVendor);

        // Notify customer
        $this->notifyCustomer($orderVendor, 'Pesanan Dikirim', 'Pesanan Anda telah dikirim. No Resi: '.$this->tracking_number);

        session()->flash('success', 'Pesanan berhasil dikirim dengan resi: '.$this->tracking_number);
    }

    public function confirmDelivery(): void
    {
        $orderVendor = $this->orderVendor;

        if (! $orderVendor || $orderVendor->status !== OrderVendorStatus::Shipped) {
            session()->flash('error', 'Pesanan tidak bisa dikonfirmasi tiba saat ini.');

            return;
        }

        $delivered = DB::transaction(function () use ($orderVendor): bool {
            $lockedOrderVendor = OrderVendor::query()
                ->whereKey($orderVendor->id)
                ->where('status', OrderVendorStatus::Shipped)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrderVendor) {
                return false;
            }

            $shipment = $lockedOrderVendor->shipment()->lockForUpdate()->first();
            if (! $shipment) {
                return false;
            }

            $shipment->update([
                'status' => ShipmentStatus::Delivered,
                'delivered_at' => now(),
            ]);
            $lockedOrderVendor->update([
                'status' => OrderVendorStatus::Delivered,
                'vendor_confirmed_at' => now(),
            ]);

            return true;
        });

        if (! $delivered) {
            session()->flash('error', 'Status pesanan sudah berubah atau data pengiriman tidak tersedia.');

            return;
        }

        $orderVendor->refresh();

        // Notify customer untuk konfirmasi penerimaan
        $this->notifyCustomer(
            $orderVendor,
            'Pesanan Tiba — Konfirmasi Penerimaan',
            'Vendor mengkonfirmasi pesanan Anda telah tiba. Silakan konfirmasi penerimaan di halaman detail pesanan Anda.'
        );

        session()->flash('success', 'Pesanan dikonfirmasi tiba. Menunggu konfirmasi penerimaan dari pembeli.');
    }

    protected function updateMainOrderStatus(OrderVendor $orderVendor): void
    {
        $order = $orderVendor->order;
        if (! $order) {
            return;
        }

        $allVendorOrders = $order->orderVendors()->get();

        // Jika semua shipped/delivered/completed → main order shipped
        $shippedStatuses = [OrderVendorStatus::Shipped, OrderVendorStatus::Delivered, OrderVendorStatus::Completed];
        if ($allVendorOrders->every(fn ($ov) => in_array($ov->status, $shippedStatuses))) {
            $order->update(['status' => 'shipped']);
        }

        // Jika semua sudah completed → main order completed
        if ($allVendorOrders->every(fn ($ov) => $ov->status === OrderVendorStatus::Completed)) {
            $order->update(['status' => 'completed']);
        }
    }

    protected function notifyCustomer(OrderVendor $orderVendor, string $title, string $message): void
    {
        $customer = $orderVendor->order?->user;
        if (! $customer) {
            return;
        }

        try {
            $customer->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\\Notifications\\OrderStatusUpdated',
                'data' => json_encode([
                    'title' => $title,
                    'message' => $message,
                    'order_id' => $orderVendor->order_id,
                    'order_number' => $orderVendor->order?->order_number,
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi status pesanan.', [
                'order_vendor_id' => $orderVendor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('vendor.dashboard.order-detail-page');
    }
}
