<section>
    @livewire('navbar')

    <div class="min-h-screen p-12 flex gap-8">
        <div class="flex-1">
            @livewire('vendor.dashboard.sidebar', ['tab' => $tab])
        </div>

        <div class="flex-3">
            @if ($tab === 'overview')
                @livewire('vendor.dashboard.overview-page')
            @elseif ($tab === 'profile')
                @livewire('vendor.dashboard.profile-page')
            @elseif ($tab === 'orders')
                @livewire('vendor.dashboard.order-page')
            @elseif ($tab === 'order-detail' && $order_id)
                @livewire('vendor.dashboard.order-detail-page', ['orderId' => $order_id], key('order-' . $order_id))
            @elseif ($tab === 'products')
                @livewire('vendor.dashboard.product-page')
            @elseif ($tab === 'product-form')
                @livewire('vendor.dashboard.product-form-page', ['productId' => $product_id], key('product-' . ($product_id ?? 'new')))
            @elseif ($tab === 'wallet')
                @livewire('vendor.dashboard.wallet-page')
            @elseif ($tab === 'bank-accounts')
                @livewire('vendor.dashboard.bank-account-page')
            @elseif ($tab === 'shipments')
                @livewire('vendor.dashboard.shipment-page')
            @else
                @livewire('vendor.dashboard.overview-page')
            @endif
        </div>
    </div>

    @include('layouts.footter')
</section>
