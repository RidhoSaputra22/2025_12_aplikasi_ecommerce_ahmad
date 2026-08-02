<?php

namespace App\Livewire\ShipParty\Dashboard;

use App\Enums\OrderVendorStatus;
use App\Models\OrderVendor;
use App\Services\ShipmentWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class OrderDetailPage extends Component
{
    public ?int $orderId = null;

    public ?string $tracking_number = null;

    protected ShipmentWorkflowService $shipmentWorkflowService;

    public function boot(ShipmentWorkflowService $shipmentWorkflowService): void
    {
        $this->shipmentWorkflowService = $shipmentWorkflowService;
    }

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderVendorProperty(): ?OrderVendor
    {
        $courier = Auth::user()?->managedShipmentCourier;

        if (! $this->orderId || ! $courier) {
            return null;
        }

        return OrderVendor::with([
            'order.user',
            'order.payment',
            'orderItems.productVariant.product.productImages',
            'orderItems.productVariant.product.category',
            'shipment.shipmentAddress',
            'shipment.shipmentCourier',
            'vendor',
        ])
            ->whereKey($this->orderId)
            ->whereHas('shipment', fn ($query) => $query->where('shipment_courier_id', $courier->id))
            ->first();
    }

    public function shipOrder(): void
    {
        $orderVendor = $this->orderVendor;

        if (! $orderVendor || $orderVendor->status !== OrderVendorStatus::Processed) {
            session()->flash('error', 'Pesanan tidak bisa dikirim.');

            return;
        }

        $trackingNumber = $this->tracking_number ?: $this->shipmentWorkflowService->generateTrackingNumber($orderVendor->shipment);
        $shipped = $this->shipmentWorkflowService->shipProcessedOrder($orderVendor, $trackingNumber);

        if (! $shipped) {
            session()->flash('error', 'Pesanan gagal dikirim atau status sudah berubah.');

            return;
        }

        $this->tracking_number = $trackingNumber;
        $orderVendor->refresh();
        $this->updateMainOrderStatus($orderVendor);

        $this->notifyCustomer(
            $orderVendor,
            'Pesanan Dikirim Ekspedisi',
            'Pihak kapal telah mengirimkan pesanan Anda. No Resi: '.$trackingNumber
        );

        session()->flash('success', 'Pesanan berhasil dikirim dengan resi: '.$trackingNumber);
    }

    public function confirmDelivery(): void
    {
        $orderVendor = $this->orderVendor;

        if (! $orderVendor || $orderVendor->status !== OrderVendorStatus::Shipped) {
            session()->flash('error', 'Pesanan tidak bisa dikonfirmasi tiba saat ini.');

            return;
        }

        $delivered = $this->shipmentWorkflowService->confirmDelivered($orderVendor);

        if (! $delivered) {
            session()->flash('error', 'Status pesanan sudah berubah atau data pengiriman tidak tersedia.');

            return;
        }

        $orderVendor->refresh();

        $this->notifyCustomer(
            $orderVendor,
            'Pesanan Tiba di Tujuan',
            'Pihak kapal mengkonfirmasi paket Anda telah tiba. Silakan konfirmasi penerimaan di halaman pesanan.'
        );

        session()->flash('success', 'Paket berhasil dikonfirmasi tiba di tujuan.');
    }

    protected function updateMainOrderStatus(OrderVendor $orderVendor): void
    {
        $order = $orderVendor->order;

        if (! $order) {
            return;
        }

        $allVendorOrders = $order->orderVendors()->get();
        $shippedStatuses = [OrderVendorStatus::Shipped, OrderVendorStatus::Delivered, OrderVendorStatus::Completed];

        if ($allVendorOrders->every(fn ($item) => in_array($item->status, $shippedStatuses, true))) {
            $order->update(['status' => 'shipped']);
        }

        if ($allVendorOrders->every(fn ($item) => $item->status === OrderVendorStatus::Completed)) {
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
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim notifikasi status pengiriman pihak kapal.', [
                'order_vendor_id' => $orderVendor->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('ship-party.dashboard.order-detail-page');
    }
}
