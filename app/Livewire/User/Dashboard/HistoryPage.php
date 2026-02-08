<?php

namespace App\Livewire\User\Dashboard;

use App\Models\Order;
use Livewire\Component;
use App\Enums\OrderStatus;
use App\Enums\OrderPaymentStatus;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class HistoryPage extends Component
{
    use WithPagination;

    public ?string $selectedOrderStatus = null;
    public ?string $selectedPaymentStatus = null;

    protected $listeners = [
        'payment-proof:uploaded' => '$refresh',
    ];

    public function viewOrder(int $orderId): void
    {
        $this->redirectRoute('user.dashboard', ['tab' => 'order-detail', 'order_id' => $orderId]);
    }

    public function openPaymentProofModal(int $orderId): void
    {
        $this->dispatch(
            'openModal',
            component: 'user.dashboard.payment-proof-upload',
            arguments: ['orderId' => $orderId],
            title: 'Upload Bukti Pembayaran',
            maxWidth: '3xl',
        );
    }

    public function render()
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
        }

        $orders = Order::with(['payment', 'orderVendors.orderItems.productVariant.product.productImages', 'orderVendors.orderItems.productVariant.product.category'])
            ->where('user_id', $userId)
            ->when($this->selectedOrderStatus, function ($query) {
                $query->where('status', $this->selectedOrderStatus);
            })
            ->when($this->selectedPaymentStatus, function ($query) {
                $query->where('payment_status', $this->selectedPaymentStatus);
            })
            ->latest('created_at')
            ->paginate(5);

        $orderStatusOptions = OrderStatus::asArray();
        $paymentStatusOptions = OrderPaymentStatus::asSelectArray();

        return view('user.dashboard.history-page', [
            'orders' => $orders,
            'orderStatusOptions' => $orderStatusOptions,
            'paymentStatusOptions' => $paymentStatusOptions,
        ]);
    }
}
