<?php

namespace App\Livewire\User\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationPage extends Component
{
    use WithPagination;

    public function markAsRead(string $notificationId): void
    {
        if (!Auth::check()) {
            return;
        }

        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->update(['read_at' => now()]);
        }
    }

    public function markAllAsRead(): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
        }

        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('user.dashboard.notification-page', [
            'notifications' => $notifications,
        ]);
    }
}
