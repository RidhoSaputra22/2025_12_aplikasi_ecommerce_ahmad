<?php

namespace App\Console\Commands;

use App\Enums\OrderVendorStatus;
use App\Enums\ShipmentStatus;
use App\Events\ShipPositionUpdated;
use App\Models\Shipment;
use App\Services\ShipmentTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BroadcastShipPositions extends Command
{
    protected $signature = 'shipments:broadcast-positions';

    protected $description = 'Broadcast live ship positions for all active shipments and auto-deliver completed ones';

    public function handle(ShipmentTrackingService $trackingService): int
    {
        $shipments = Shipment::with(['orderVendor.vendor', 'orderVendor.order.user'])
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
                if ($this->autoDeliverShipment($shipment)) {
                    $deliveredCount++;
                }
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
    protected function autoDeliverShipment(Shipment $shipment): bool
    {
        $orderVendor = DB::transaction(function () use ($shipment) {
            $lockedShipment = Shipment::query()
                ->whereKey($shipment->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedShipment || $lockedShipment->status !== ShipmentStatus::Shipped) {
                return null;
            }

            $orderVendor = $lockedShipment->orderVendor()
                ->lockForUpdate()
                ->first();

            if (! $orderVendor || $orderVendor->status !== OrderVendorStatus::Shipped) {
                return null;
            }

            $lockedShipment->update([
                'status' => ShipmentStatus::Delivered,
                'delivered_at' => now(),
            ]);

            // Perjalanan selesai berarti barang tiba, tetapi penyelesaian dan
            // pencairan tetap menunggu konfirmasi customer.
            $orderVendor->update([
                'status' => OrderVendorStatus::Delivered,
                'vendor_confirmed_at' => now(),
            ]);

            return $orderVendor->fresh(['vendor', 'order.user']);
        });

        if (! $orderVendor) {
            return false;
        }

        $this->notifyCustomer($orderVendor);

        return true;
    }

    /**
     * Send notification to customer about delivery.
     */
    protected function notifyCustomer($orderVendor): void
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
                    'title' => 'Pesanan Tiba',
                    'message' => 'Pesanan Anda dari '.($orderVendor->vendor?->store_name ?? 'vendor').' telah tiba di tujuan.',
                    'order_id' => $orderVendor->order_id,
                    'order_number' => $orderVendor->order?->order_number,
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi kedatangan otomatis.', [
                'order_vendor_id' => $orderVendor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
