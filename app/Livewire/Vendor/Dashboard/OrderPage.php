<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Models\OrderVendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OrderPage extends Component
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

    public function processOrder(int $orderVendorId): void
    {
        $vendor = Auth::user()?->vendor;
        if (! $vendor) {
            return;
        }

        $orderVendor = OrderVendor::where('id', $orderVendorId)
            ->where('vendor_id', $vendor->id)
            ->where('status', OrderVendorStatus::Pending)
            ->where(function ($query) {
                $query
                    ->whereHas('order', fn ($orderQuery) => $orderQuery->where('payment_status', OrderPaymentStatus::Paid))
                    ->orWhereHas('order.payment', fn ($paymentQuery) => $paymentQuery->where('status', PaymentStatus::Success));
            })
            ->first();

        if (! $orderVendor) {
            session()->flash('error', 'Pesanan tidak ditemukan atau tidak bisa diproses.');

            return;
        }

        $orderVendor->update(['status' => OrderVendorStatus::Processed]);
        session()->flash('success', 'Pesanan berhasil diproses.');
    }

    public function render()
    {
        $vendor = Auth::user()?->vendor;

        if (! $vendor) {
            return view('vendor.dashboard.order-page', [
                'orderVendors' => collect(),
                'statusOptions' => [],
            ]);
        }

        $orderVendors = OrderVendor::with([
            'order.user',
            'order.payment',
            'orderItems.productVariant.product.productImages',
            'orderItems.productVariant.product.category',
            'shipment.shipmentCourier',
        ])
            ->where('vendor_id', $vendor->id)
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('order_number', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('created_at')
            ->paginate(10);

        $statusOptions = array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], OrderVendorStatus::cases());

        return view('vendor.dashboard.order-page', [
            'orderVendors' => $orderVendors,
            'statusOptions' => $statusOptions,
        ]);
    }
}
