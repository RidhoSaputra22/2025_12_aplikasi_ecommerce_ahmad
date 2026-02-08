<section>
    @livewire('navbar')

    <div class="min-h-screen p-12 flex gap-8">
        <div class="flex-1">
            @livewire('user.dashboard.sidebar', ['tab' => $tab])
        </div>

        <div class="flex-3">
            @if ($tab === 'history')
                @livewire('user.dashboard.history-page')
            @elseif ($tab === 'order-detail' && $order_id)
                @livewire('user.dashboard.order-detail-page', ['orderId' => $order_id], key('order-' . $order_id))
            @elseif ($tab === 'notifications')
                @livewire('user.dashboard.notification-page')
            @else
                @livewire('user.dashboard.profile-page')
            @endif
        </div>
    </div>

    @include('layouts.footter')
</section>
