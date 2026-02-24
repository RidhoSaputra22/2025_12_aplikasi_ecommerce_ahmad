<?php

namespace App\Console\Commands;

use App\Enums\OrderVendorStatus;
use App\Enums\ShipmentStatus;
use App\Events\ShipPositionUpdated;
use App\Models\Shipment;
use App\Services\ShipmentTrackingService;
use Illuminate\Console\Command;

class BroadcastShipPositions extends Command
{
    protected $signature = 'shipments:broadcast-positions';

    protected $description = 'Broadcast live ship positions for all active shipments and auto-deliver completed ones';

    public function handle(ShipmentTrackingService $trackingService): int
    {
        $shipments = Shipment::with(['orderVendor.vendor.vendorWallet', 'orderVendor.order'])
            ->where('status', ShipmentStatus::Shipped)
            ->whereNotNull('shipped_at')
            ->get();

        if ($shipments->isEmpty()) {
            $this->info('No active shipments to broadcast.');
            return self::SUCCESS;
        }

        $broadcastCount = 0;
        $deliveredCount = 0;

        foreach ($shipments as $shipment) {
            $position = $trackingService->calculateShipPosition($shipment);

            // Auto-deliver if journey is complete (6 hours elapsed)
            if ($position['is_arrived']) {
                $this->autoDeliverShipment($shipment);
                $deliveredCount++;
            }

            // Broadcast position update
            event(new ShipPositionUpdated(
                shipment_id: $shipment->id,
                lat: $position['lat'],
                lng: $position['lng'],
                progress: $position['progress'],
                heading: $position['heading'],
                eta: $position['eta'],
                is_arrived: $position['is_arrived'],
                distance_remaining_km: $position['distance_remaining_km'],
                total_distance_km: $position['total_distance_km'],
            ));

            $broadcastCount++;
        }

        $this->info("Broadcasted {$broadcastCount} positions. Auto-delivered: {$deliveredCount}.");

        return self::SUCCESS;
    }

    /**
     * Auto-deliver a shipment that has completed its journey.
     */
    protected function autoDeliverShipment(Shipment $shipment): void
    {
        // Update shipment status
        $shipment->update([
            'status' => ShipmentStatus::Delivered,
            'delivered_at' => now(),
        ]);

        $orderVendor = $shipment->orderVendor;
        if (!$orderVendor) {
            return;
        }

        // Only update if currently shipped
        if ($orderVendor->status !== OrderVendorStatus::Shipped) {
            return;
        }

        // Update order vendor status
        $orderVendor->update(['status' => OrderVendorStatus::Completed]);

        // Credit vendor wallet
        $this->creditVendorWallet($orderVendor);

        // Update main order status
        $this->updateMainOrderStatus($orderVendor);

        // Notify customer
        $this->notifyCustomer($orderVendor);
    }

    /**
     * Credit vendor wallet with the order subtotal.
     */
    protected function creditVendorWallet($orderVendor): void
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
            'description' => 'Pendapatan dari pesanan #' . $orderVendor->order?->order_number . ' (auto-delivered)',
            'reference_id' => $orderVendor->id,
        ]);
    }

    /**
     * Update main order status based on all vendor orders.
     */
    protected function updateMainOrderStatus($orderVendor): void
    {
        $order = $orderVendor->order;
        if (!$order) {
            return;
        }

        $allVendorOrders = $order->orderVendors()->get();

        if ($allVendorOrders->every(fn($ov) => in_array($ov->status, [OrderVendorStatus::Shipped, OrderVendorStatus::Completed]))) {
            $order->update(['status' => 'shipped']);
        }

        if ($allVendorOrders->every(fn($ov) => $ov->status === OrderVendorStatus::Completed)) {
            $order->update(['status' => 'completed']);
        }
    }

    /**
     * Send notification to customer about delivery.
     */
    protected function notifyCustomer($orderVendor): void
    {
        $customer = $orderVendor->order?->user;
        if (!$customer) {
            return;
        }

        $customer->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\OrderStatusUpdated',
            'data' => json_encode([
                'title' => 'Pesanan Tiba',
                'message' => 'Pesanan Anda dari ' . ($orderVendor->vendor?->store_name ?? 'vendor') . ' telah tiba di tujuan.',
                'order_id' => $orderVendor->order_id,
                'order_number' => $orderVendor->order?->order_number,
            ]),
        ]);
    }
}
