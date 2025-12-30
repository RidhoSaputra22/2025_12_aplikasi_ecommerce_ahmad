<section class=" flex  gap-10 p-12 space-y-14  " wire:ignore>
    <div class="space-y-5 flex-1 pt-8" data-aos="fade-up">
        <h1 class="text-6xl/tight font-semibold">Membawa Lokal Untuk Indonesia</h1>
        <p class="text-lg/loose font-light">
            Kami berkomitmen untuk mempromosikan produk-produk lokal dari berbagai daerah di Indonesia, membantu
            pengrajin
            dan produsen kecil untuk menjangkau pasar yang lebih luas serta meningkatkan perekonomian lokal.


        </p>
        @component('components.form.button', [
        'label' => 'Pelajari Lebih Lanjut >>'
        ])

        @endcomponent
    </div>
    <div class="flex-1 " data-aos="fade-up">
        <img src="{{ asset('images/about-us.png') }}" alt="" srcset="" class="rounded-md">
    </div>






</section>
