<section>
    @livewire('navbar')

    <div class="min-h-screen p-12 flex gap-8">
        <div class="flex-1">
            @livewire('ship-party.dashboard.sidebar', ['tab' => $tab])
        </div>

        <div class="flex-3">
            @if ($tab === 'overview')
                @livewire('ship-party.dashboard.overview-page')
            @elseif ($tab === 'shipments')
                @livewire('ship-party.dashboard.shipment-page')
            @elseif ($tab === 'order-detail' && $order_id)
                @livewire('ship-party.dashboard.order-detail-page', ['orderId' => $order_id], key('ship-party-order-' . $order_id))
            @elseif ($tab === 'tracking')
                @livewire('ship-party.dashboard.tracking-page')
            @else
                @livewire('ship-party.dashboard.overview-page')
            @endif
        </div>
    </div>

    @include('layouts.footter')
</section>
