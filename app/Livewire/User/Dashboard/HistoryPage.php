<?php

namespace App\Livewire\User\Dashboard;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class HistoryPage extends Component
{
    use WithPagination;

    public function render()
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
        }

        $orders = Order::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10) ;

        return view('user.dashboard.history-page', [
            'orders' => $orders,
        ]);
    }
}
