<section class="flex h-screen  overflow-hidden">
    <div class="flex-2 p-12 ">
        <div>
            <h1 class="text-2xl/tight font-semibold">Toko Ga`de</h1>
            <p class="text-lg font-light">Melayani Sejak 2010</p>
        </div>
        <form action="" class="mt-30 space-y-6 max-w-xl mx-auto" wire:submit.prevent="login">

            <div class="">
                <h1 class="text-5xl/loose">Login</h1>
                <p class="text-lg font-light">Silakan masukkan email dan kata sandi Anda untuk masuk.</p>
                <p class="text-sm text-gray-500 mt-1">Anda dapat login sebagai <strong>Customer</strong>, <strong>Vendor</strong>, atau <strong>Admin</strong>.</p>
            </div>

            <div>
                @if (session()->has('message'))
                <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 mb-2">
                    {{ session('message') }}
                </div>
                @endif
                @if (session()->has('error'))
                <div class="p-3 rounded-lg border border-red-200 bg-red-50 text-red-700 mb-2">
                    {{ session('error') }}
                </div>
                @endif
            </div>
            @component('components.form.input', [
            'label' => 'Email',
            'type' => 'email',
            'wireModel' => 'email',
            'placeholder' => 'Masukkan email Anda',
            'required' => true,
            ])

            @endcomponent

            @component('components.form.input', [
            'label' => 'Kata Sandi (minimal 8 karakter)',
            'type' => 'password',
            'wireModel' => 'password',
            'placeholder' => 'Masukkan kata sandi Anda',
            'required' => true,
            ])

            @endcomponent
            @component('components.form.button', [
            'label' => 'Masuk',
            'class' => 'w-full bg-primary text-white',
            'wireLoadingTarget' => 'login',
            'wireLoadingClass' => 'opacity-70 cursor-not-allowed',
            ])

            @endcomponent
            <div>
                <p class=" mt-6">Belum punya akun?
                    <a href="{{ route('user.register') }}" class="text-primary font-semibold hover:underline">Daftar
                        sekarang</a>
                </p>
            </div>
        </form>
    </div>
    <div class="flex-3 bg-primary py-12 px-24 text-white space-y-4 ">
        <div class="space-y-3">
            <h1 class="text-6xl/snug font-semibold">Bersama Membantu Membangun Desa</h1>
            <p class="text-lg font-light">
                Toko Ga`de hadir untuk mendukung perekonomian desa dengan menyediakan platform e-commerce yang
                memudahkan
                masyarakat desa dalam mengakses produk berkualitas dari berbagai daerah. Mari bergabung bersama kami
                dalam
                memberdayakan potensi lokal dan meningkatkan kesejahteraan komunitas desa.
            </p>
        </div>
        <img src="{{asset('images/about-us.png')}}" alt="" class="w-full aspect-video ">
    </div>



</section>
