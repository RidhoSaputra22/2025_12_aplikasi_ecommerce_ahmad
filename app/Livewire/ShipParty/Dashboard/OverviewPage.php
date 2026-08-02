<?php

namespace App\Livewire\ShipParty\Dashboard;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OverviewPage extends Component
{
    public function render()
    {
        $courier = Auth::user()?->managedShipmentCourier;

        if (! $courier) {
            return view('ship-party.dashboard.overview-page', [
                'courier' => null,
                'stats' => [],
                'recentShipments' => collect(),
            ]);
        }

        $shipments = Shipment::query()->where('shipment_courier_id', $courier->id);

        return view('ship-party.dashboard.overview-page', [
            'courier' => $courier,
            'stats' => [
                'totalShipments' => (clone $shipments)->count(),
                'pendingShipments' => (clone $shipments)->where('status', ShipmentStatus::Pending)->count(),
                'shippedShipments' => (clone $shipments)->where('status', ShipmentStatus::Shipped)->count(),
                'deliveredShipments' => (clone $shipments)->where('status', ShipmentStatus::Delivered)->count(),
            ],
            'recentShipments' => Shipment::with([
                'orderVendor.order.user',
                'orderVendor.orderItems.productVariant.product',
                'shipmentAddress',
            ])
                ->where('shipment_courier_id', $courier->id)
                ->latest('created_at')
                ->take(5)
                ->get(),
        ]);
    }
}
