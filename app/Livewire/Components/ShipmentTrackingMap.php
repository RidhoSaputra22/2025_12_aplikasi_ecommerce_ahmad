<?php

namespace App\Livewire\Components;

use App\Models\Shipment;
use App\Services\ShipmentTrackingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShipmentTrackingMap extends Component
{
    public int $shipmentId;

    public array $origin = [];

    public array $destination = [];

    public array $currentPosition = [];

    public float $progress = 0;

    public float $heading = 0;

    public ?string $eta = null;

    public ?string $shippedAt = null;

    public bool $isArrived = false;

    public float $distanceRemainingKm = 0;

    public float $totalDistanceKm = 0;

    public function mount(int $shipmentId): void
    {
        abort_unless(Auth::check(), 403);

        $this->shipmentId = $shipmentId;

        $trackingService = app(ShipmentTrackingService::class);

        $this->origin = $trackingService->getOrigin();
        $this->destination = $trackingService->getDestination();
        $this->totalDistanceKm = $trackingService->getTotalDistanceKm();

        $shipment = Shipment::query()
            ->whereKey($shipmentId)
            ->where(function ($query) {
                $user = Auth::user();

                if ($user?->role?->name === 'admin') {
                    return;
                }

                $query
                    ->whereHas('orderVendor.order', fn ($orderQuery) => $orderQuery->where('user_id', Auth::id()))
                    ->orWhereHas('orderVendor.vendor', fn ($vendorQuery) => $vendorQuery->where('user_id', Auth::id()));
            })
            ->firstOrFail();

        if ($shipment->shipped_at) {
            $position = $trackingService->calculateShipPosition($shipment);

            $this->currentPosition = ['lat' => $position['lat'], 'lng' => $position['lng']];
            $this->progress = $position['progress'];
            $this->heading = $position['heading'];
            $this->eta = $position['eta'];
            $this->shippedAt = $position['shipped_at'];
            $this->isArrived = $position['is_arrived'];
            $this->distanceRemainingKm = $position['distance_remaining_km'];
        } else {
            $this->currentPosition = ['lat' => $this->origin['lat'], 'lng' => $this->origin['lng']];
            $this->heading = $trackingService->calculateDistanceKm(
                $this->origin['lat'], $this->origin['lng'],
                $this->destination['lat'], $this->destination['lng']
            );
        }
    }

    public function render()
    {
        return view('livewire.components.shipment-tracking-map');
    }
}
