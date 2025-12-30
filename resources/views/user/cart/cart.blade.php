<section>
    @livewire('navbar')
    <div class="p-12 ">
        <div class="min-h-screen flex  gap-10">
            <div class="flex-3">
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold">Keranjang Belanja</h1>
                    <p class=" font-light">Pastikan pesanan Anda sudah benar sebelum melakukan pembayaran.</p>
                </div>
                @livewire('user.cart.cart-details')
            </div>
            <div class="flex-1">
                @livewire('user.cart.cart-summary')
            </div>
        </div>
    </div>
    @include('layouts.footter')
</section>
