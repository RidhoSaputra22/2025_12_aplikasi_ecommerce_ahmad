<section class="flex h-screen  overflow-hidden">
    <div class="flex-2 p-12 ">
        <div>
            <h1 class="text-2xl/tight font-semibold">Toko Desa</h1>
            <p class="text-lg font-light">Melayani Sejak 2010</p>
        </div>
        <form action="" class="mt-30 space-y-6 max-w-xl mx-auto" wire:submit.prevent="regist">

            <div class="">
                <h1 class="text-5xl/loose">Register</h1>
                <p class="text-lg font-light">Silakan masukkan email dan kata sandi Anda untuk mendaftar.</p>
            </div>

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

            @component('components.form.input', [
            'label' => 'Kata Sandi',
            'type' => 'password',
            'wireModel' => 'password',
            'placeholder' => 'Masukkan kata sandi Anda',
            'required' => true,
            ])

            @endcomponent
            @component('components.form.button', [
            'label' => 'Masuk',
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