<?php

namespace App\Services;

use App\Enums\OrderVendorStatus;
use App\Enums\ShipmentStatus;
use App\Models\OrderVendor;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentWorkflowService
{
    public function processVendorOrder(OrderVendor $orderVendor): array
    {
        return DB::transaction(function () use ($orderVendor): array {
            $lockedOrderVendor = OrderVendor::query()
                ->with(['order.payment', 'shipment.shipmentCourier'])
                ->whereKey($orderVendor->id)
                ->where('status', OrderVendorStatus::Pending)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrderVendor || ! $lockedOrderVendor->order?->hasConfirmedPayment()) {
                return ['success' => false];
            }

            $lockedOrderVendor->update(['status' => OrderVendorStatus::Processed]);

            return [
                'success' => true,
                'auto_shipped' => false,
            ];
        });
    }

    public function shipProcessedOrder(OrderVendor $orderVendor, string $trackingNumber): bool
    {
        return DB::transaction(function () use ($orderVendor, $trackingNumber): bool {
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
                'tracking_number' => $trackingNumber,
                'status' => ShipmentStatus::Shipped,
                'shipped_at' => now(),
            ]);

            return true;
        });
    }

    public function confirmDelivered(OrderVendor $orderVendor): bool
    {
        return DB::transaction(function () use ($orderVendor): bool {
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
    }

    public function generateTrackingNumber(Shipment $shipment): string
    {
        $code = Str::upper($shipment->shipmentCourier?->code ?? 'KPL');

        return sprintf('%s-%s', $code, Str::upper(Str::random(10)));
    }
}
