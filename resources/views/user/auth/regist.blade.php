<section class="flex h-screen  overflow-hidden">
    <div class="flex-2 p-12 ">
        <div>
            <h1 class="text-2xl/tight font-semibold">Toko Desa</h1>
            <p class="text-lg font-light">Melayani Sejak 2010</p>
        </div>
        <form action="" class="mt-20 space-y-6 max-w-xl mx-auto" wire:submit.prevent="regist">

            <div class="">
                <h1 class="text-5xl/loose">Register</h1>
                <p class="text-lg font-light">Lengkapi data diri Anda untuk mendaftar akun baru.</p>
            </div>

            <div class="flex gap-5">
                @component('components.form.input', [
                'label' => 'Nama Lengkap',
                'type' => 'text',
                'wireModel' => 'nama',
                'placeholder' => 'Masukkan nama lengkap Anda',
                'required' => true,

                ])
                @endcomponent

                @component('components.form.input', [
                'label' => 'Email',
                'type' => 'email',
                'wireModel' => 'email',
                'placeholder' => 'Masukkan email Anda',
                'required' => true,
                ])
                @endcomponent
            </div>

            @component('components.form.input', [
            'label' => 'No. Telepon',
            'type' => 'text',
            'wireModel' => 'phone',
            'placeholder' => 'Masukkan nomor telepon Anda',
            'required' => false,
            ])
            @endcomponent

            @component('components.form.select', [
            'label' => 'Daftar Sebagai',
            'wireModel' => 'role',
            'options' => [
            ['label' => 'Customer (Pembeli)', 'value' => 'customer'],
            ['label' => 'Vendor (Penjual)', 'value' => 'vendor'],
            ],
            'default' => [
            'label' => '-- Pilih Peran --',
            'value' => '',
            ],
            ])
            @endcomponent

            @component('components.form.input', [
            'label' => 'Kata Sandi (minimal 8 karakter)',
            'type' => 'password',
            'wireModel' => 'password',
            'placeholder' => 'Masukkan kata sandi minimal 8 karakter',
            'required' => true,
            ])

            @endcomponent
            @component('components.form.button', [
            'label' => 'Daftar',
            'class' => 'w-full bg-primary text-white',
            'wireLoadingTarget' => 'regist',
            'wireLoadingClass' => 'opacity-70 cursor-not-allowed',


            ])

            @endcomponent
            <div>
                <p class=" mt-6">Sudah punya akun?
                    <a href="{{ route('user.login') }}" class="text-primary font-semibold hover:underline">Masuk
                        sekarang</a>
                </p>
            </div>
        </form>
    </div>
    <div class="flex-3 bg-primary py-12 px-24 text-white space-y-4 ">
        <div class="space-y-3">
            <h1 class="text-6xl/snug font-semibold">Bersama Membantu Membangun Desa</h1>
            <p class="text-lg font-light">
                Toko Desa hadir untuk mendukung perekonomian desa dengan menyediakan platform e-commerce yang
                memudahkan
                masyarakat desa dalam mengakses produk berkualitas dari berbagai daerah. Mari bergabung bersama kami
                dalam
                memberdayakan potensi lokal dan meningkatkan kesejahteraan komunitas desa.
            </p>
        </div>
        <img src="{{asset('images/about-us.png')}}" alt="" class="w-full aspect-video ">
    </div>



</section>