<?php

namespace App\Livewire\User\Dashboard;

use App\Models\Order;
use Livewire\Component;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class HistoryPage extends Component
{
    use WithPagination;

    public ?string $selectedOrderStatus = null;
    public ?string $selectedPaymentStatus = null;


    public function render()
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
        }

        $orders = Order::with(['orderVendors'])
            ->where('user_id', $userId)
            ->when($this->selectedOrderStatus, function ($query) {
                $query->where('status', $this->selectedOrderStatus);
            })
            ->when($this->selectedPaymentStatus, function ($query) {
                $query->where('payment_status', $this->selectedPaymentStatus);
            })
            ->latest('created_at')
            ->paginate(1);

        $orderStatusOptions = OrderStatus::asArray();
        $paymentStatusOptions = PaymentStatus::asArray();

        // dd($orderStatusOptions);

        return view('user.dashboard.history-page', [
            'orders' => $orders,
            'orderStatusOptions' => $orderStatusOptions,
            'paymentStatusOptions' => $paymentStatusOptions,
        ]);
    }
}
