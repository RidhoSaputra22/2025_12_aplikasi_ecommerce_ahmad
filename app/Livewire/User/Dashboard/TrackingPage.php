<?php

namespace App\Livewire\User\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TrackingPage extends Component
{
    use WithPagination;

    public ?string $search = null;
    public ?string $selectedStatus = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function viewOrder(int $orderId): void
    {
        $this->redirectRoute('user.dashboard', ['tab' => 'order-detail', 'order_id' => $orderId]);
    }

    public function render()
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
        }

        $orders = Order::with([
            'orderVendors.vendor',
            'orderVendors.shipment.shipmentCourier',
            'orderVendors.shipment.shipmentAddress',
            'orderVendors.orderItems.productVariant.product.productImages',
        ])
            ->where('user_id', $userId)
            ->whereIn('status', [
                OrderStatus::Paid,
                OrderStatus::Shipped,
                OrderStatus::Completed,
            ])
            ->when($this->selectedStatus, function ($query) {
                $query->whereHas('orderVendors.shipment', function ($q) {
                    $q->where('status', $this->selectedStatus);
                });
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('orderVendors.shipment', function ($sq) {
                            $sq->where('tracking_number', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest('created_at')
            ->paginate(5);

        $statusOptions = array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], ShipmentStatus::cases());

        return view('user.dashboard.tracking-page', [
            'orders' => $orders,
            'statusOptions' => $statusOptions,
        ]);
    }
}
