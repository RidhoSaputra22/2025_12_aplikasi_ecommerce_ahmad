<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\ShipmentStatus;
use App\Models\OrderVendor;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ShipmentPage extends Component
{
    use WithPagination;

    public ?string $selectedStatus = null;
    public ?string $search = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
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
            return view('vendor.dashboard.shipment-page', [
                'shipments' => collect(),
                'statusOptions' => [],
            ]);
        }

        $shipments = Shipment::with([
            'orderVendor.order.user',
            'orderVendor.orderItems.productVariant.product',
            'shipmentAddress',
            'shipmentCourier',
        ])
            ->whereHas('orderVendor', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->search, function ($query) {
                $query->where('tracking_number', 'like', '%' . $this->search . '%');
            })
            ->latest('created_at')
            ->paginate(10);

        $statusOptions = array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], ShipmentStatus::cases());

        return view('vendor.dashboard.shipment-page', [
            'shipments' => $shipments,
            'statusOptions' => $statusOptions,
        ]);
    }
}
