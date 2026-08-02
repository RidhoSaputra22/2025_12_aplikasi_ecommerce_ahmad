<?php

namespace App\Livewire\ShipParty\Dashboard;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ShipmentPage extends Component
{
    use WithPagination;

    public ?string $selectedStatus = null;
    public ?string $search = null;

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function viewOrder(int $orderVendorId): void
    {
        $this->redirectRoute('ship-party.dashboard', ['tab' => 'order-detail', 'order_id' => $orderVendorId]);
    }

    public function render()
    {
        $courier = Auth::user()?->managedShipmentCourier;

        if (! $courier) {
            return view('ship-party.dashboard.shipment-page', [
                'shipments' => collect(),
                'statusOptions' => [],
            ]);
        }

        return view('ship-party.dashboard.shipment-page', [
            'shipments' => Shipment::with([
                'orderVendor.order.user',
                'orderVendor.orderItems.productVariant.product',
                'shipmentAddress',
                'shipmentCourier',
            ])
                ->where('shipment_courier_id', $courier->id)
                ->when($this->selectedStatus, fn ($query) => $query->where('status', $this->selectedStatus))
                ->when($this->search, function ($query) {
                    $query->where(function ($nested) {
                        $nested->where('tracking_number', 'like', '%'.$this->search.'%')
                            ->orWhereHas('orderVendor.order', function ($orderQuery) {
                                $orderQuery->where('order_number', 'like', '%'.$this->search.'%');
                            });
                    });
                })
                ->latest('created_at')
                ->paginate(10),
            'statusOptions' => array_map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->getLabel(),
            ], ShipmentStatus::cases()),
        ]);
    }
}
