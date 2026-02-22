<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\OrderVendorStatus;
use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TrackingPage extends Component
{
    use WithPagination;

    public ?string $selectedStatus = null;
    public ?string $search = null;
    public ?string $selectedShipmentStatus = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedShipmentStatus(): void
    {
        $this->resetPage();
    }

    public function viewOrder(int $orderVendorId): void
    {
        $this->redirectRoute('vendor.dashboard', ['tab' => 'order-detail', 'order_id' => $orderVendorId]);
    }

    public function render()
    {
        $vendor = Auth::user()?->vendor;

        if (!$vendor) {
            return view('vendor.dashboard.tracking-page', [
                'shipments' => collect(),
                'orderStatusOptions' => [],
                'shipmentStatusOptions' => [],
            ]);
        }

        $shipments = Shipment::with([
            'orderVendor.order.user',
            'orderVendor.vendor',
            'orderVendor.orderItems.productVariant.product.productImages',
            'shipmentAddress',
            'shipmentCourier',
        ])
            ->whereHas('orderVendor', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->whereHas('orderVendor', function ($q) {
                    $q->where('status', $this->selectedStatus);
                });
            })
            ->when($this->selectedShipmentStatus, function ($query) {
                $query->where('status', $this->selectedShipmentStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('tracking_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('orderVendor.order', function ($sq) {
                            $sq->where('order_number', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest('created_at')
            ->paginate(10);

        $orderStatusOptions = array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], OrderVendorStatus::cases());

        $shipmentStatusOptions = array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], ShipmentStatus::cases());

        return view('vendor.dashboard.tracking-page', [
            'shipments' => $shipments,
            'orderStatusOptions' => $orderStatusOptions,
            'shipmentStatusOptions' => $shipmentStatusOptions,
        ]);
    }
}
