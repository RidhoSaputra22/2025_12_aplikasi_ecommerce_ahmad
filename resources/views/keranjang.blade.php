@extends('layouts.app')

@section('content')
    @component('layouts.navbar')
    @endcomponent

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Breadcrumb -->
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="/"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            Home
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Keranjang</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Keranjang Section -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Keranjang</h2>

                    <!-- Cart Item -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <!-- Product Image -->
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img src="{{ Storage::url('aksesoris-1.jpg') }}" alt="Kertas Ncr Merah"
                                        class="w-full h-full object-cover">
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">Kertas Ncr Merah</h3>
                                    <p class="text-green-600 font-bold">Rp 95.000</p>
                                </div>
                            </div>

                            <!-- Quantity Controls and Remove -->
                            <div class="flex items-center space-x-3">
                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="w-8 h-8 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors"
                                        onclick="decreaseQuantity()">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <input type="number" value="1" min="1" id="quantity"
                                        class="w-12 h-8 text-center border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button
                                        class="w-8 h-8 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors"
                                        onclick="increaseQuantity()">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Remove Button -->
                                <button
                                    class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors"
                                    onclick="removeItem()">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Total Section -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center text-lg font-semibold">
                            <span class="text-gray-800">Total</span>
                            <span class="text-gray-800" id="totalPrice">Rp 95.000</span>
                        </div>
                    </div>
                </div>

                <!-- Konfirmasi Pemesanan Section -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Konfirmasi Pemesanan</h2>
                    <p class="text-gray-600 mb-6">
                        Tentukan pemesanan sebagai anggota member atau tidak, isi lengkapi data diri anda dengan
                        lengkap dan benar sebelum melakukan konfirmasi pemesanan.
                    </p>

                    <!-- Member Type Selection -->
                    <div class="space-y-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Terdaftar keanggotaan -->
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="selectMemberType('member')">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-4 0v1m4-1v1">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Terdaftar keanggotaan</h3>
                                </div>
                                <input type="radio" name="memberType" value="member" class="hidden" id="memberRadio">
                            </div>

                            <!-- Belum terdaftar -->
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="selectMemberType('guest')">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Belum terdaftar</h3>
                                </div>
                                <input type="radio" name="memberType" value="guest" class="hidden" id="guestRadio"
                                    checked>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information Form -->
                    <form class="space-y-4" id="orderForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan nama lengkap" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                                <input type="tel"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan nomor telepon" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan email" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                            <textarea rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan alamat lengkap" required></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                                <input type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan kota" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                                <input type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan kode pos" required>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="pt-6">
                            <button type="submit"
                                class="w-full bg-green-600 text-white py-3 px-6 rounded-lg text-lg font-semibold hover:bg-green-700 transition-colors">
                                Konfirmasi Pemesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Direkomendasikan Section -->
            <div class="mt-16">
                <h2 class="text-3xl font-bold text-gray-800 text-center mb-8">Direkomendasikan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('furniture-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 1</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 1.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 500.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('model-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 2</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 2.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 350.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 3</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 3.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 150.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 3</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 3.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 150.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @component('layouts.footter')
    @endcomponent

    <script>
        // Quantity Controls
        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateTotal();
            }
        }

        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            input.value = currentValue + 1;
            updateTotal();
        }

        function updateTotal() {
            const quantity = parseInt(document.getElementById('quantity').value);
            const basePrice = 95000;
            const total = quantity * basePrice;
            document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        function removeItem() {
            if (confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
                // In a real application, this would make an API call to remove the item
                alert('Item berhasil dihapus dari keranjang');
                // You could redirect to an empty cart page or refresh the page
            }
        }

        // Member Type Selection
        function selectMemberType(type) {
            // Remove active state from all cards
            document.querySelectorAll('[onclick*="selectMemberType"]').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-200');
            });

            // Add active state to selected card
            event.currentTarget.classList.remove('border-gray-200');
            event.currentTarget.classList.add('border-blue-500', 'bg-blue-50');

            // Update radio button
            if (type === 'member') {
                document.getElementById('memberRadio').checked = true;
                document.getElementById('guestRadio').checked = false;
            } else {
                document.getElementById('memberRadio').checked = false;
                document.getElementById('guestRadio').checked = true;
            }
        }

        // Form Submission
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);
            const memberType = document.querySelector('input[name="memberType"]:checked').value;

            // In a real application, you would send this data to your backend
            alert('Pesanan berhasil dikonfirmasi! Kami akan segera menghubungi Anda.');
        });

        // Initialize default selection
        document.addEventListener('DOMContentLoaded', function() {
            selectMemberType('guest');
        });
    </script>
@endsection
