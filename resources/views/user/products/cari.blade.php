<section>
    @livewire('navbar')

    {{-- Content --}}
    @include('components.product.banner')
    @include('components.product.content')

    {{-- @include('components.home.faq') --}}


    {{-- End Content --}}

    @include('layouts.footter')
</section>
