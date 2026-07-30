<?php

namespace Tests\Feature\Scenarios;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Livewire\User\Auth\Login;
use App\Livewire\User\Auth\Regist;
use App\Livewire\User\Cart\CartDetails;
use App\Livewire\User\Cart\CartSummary;
use App\Livewire\User\Dashboard\HistoryPage;
use App\Livewire\User\Dashboard\OrderDetailPage;
use App\Livewire\User\Dashboard\TrackingPage;
use App\Livewire\User\Home\Welcome;
use App\Livewire\User\Payment\PaymentPage;
use App\Livewire\User\Products\Cari;
use App\Livewire\User\Products\Detail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Concerns\BuildsEcommerceScenarios;
use Tests\Support\FakePaymentGateway;
use Tests\TestCase;

class CustomerScenarioTest extends TestCase
{
    use BuildsEcommerceScenarios;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedScenarioRoles();
        $this->app->bind(PaymentGatewayInterface::class, FakePaymentGateway::class);
    }

    public function test_01_registrasi_customer_dengan_data_lengkap(): void
    {
        Livewire::test(Regist::class)
            ->set('nama', 'Customer Satu')
            ->set('email', 'customer1@example.test')
            ->set('phone', '081234567890')
            ->set('password', 'password-aman')
            ->set('password_confirmation', 'password-aman')
            ->set('role', 'customer')
            ->call('regist')
            ->assertHasNoErrors()
            ->assertRedirect(route('user.login'));

        $user = User::query()->where('email', 'customer1@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role?->name);
        $this->assertTrue(Hash::check('password-aman', $user->password));
    }

    public function test_02_registrasi_customer_dengan_data_kosong_ditolak(): void
    {
        Livewire::test(Regist::class)
            ->set('nama', '')
            ->set('email', '')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->set('role', 'customer')
            ->call('regist')
            ->assertHasErrors(['nama', 'email', 'password', 'password_confirmation']);

        Livewire::test(Regist::class)
            ->set('nama', 'Customer Konfirmasi Salah')
            ->set('email', 'konfirmasi-salah@example.test')
            ->set('password', 'password-aman')
            ->set('password_confirmation', 'password-berbeda')
            ->set('role', 'customer')
            ->call('regist')
            ->assertHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_03_login_customer_valid_mengarah_ke_homepage(): void
    {
        $customer = $this->actor('customer', [
            'email' => 'customer-login@example.test',
            'password' => 'password-aman',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'customer-login@example.test')
            ->set('password', 'password-aman')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('welcome'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_04_login_customer_tidak_valid_ditolak(): void
    {
        $this->actor('customer', [
            'email' => 'customer-invalid@example.test',
            'password' => 'password-benar',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'customer-invalid@example.test')
            ->set('password', 'password-salah')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_05_homepage_menampilkan_navigasi_dan_banner(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Toko Ga')
            ->assertSee('Selamat Datang di website Toko Ga');

        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $product] = $this->productFor($vendor, ['name' => 'Produk Tanpa Gambar']);

        Livewire::test(Welcome::class)
            ->call('loadInitialData')
            ->assertSee($product->name)
            ->assertSee('images/product-paceholder.jpg');
    }

    public function test_06_pencarian_produk_menampilkan_kata_kunci_yang_sesuai(): void
    {
        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $matched] = $this->productFor($vendor, ['name' => 'Kopi Arabika Desa']);
        $this->productFor($vendor, ['name' => 'Kerajinan Bambu']);

        Livewire::test(Cari::class)
            ->set('search', 'Arabika')
            ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$matched->id]);
    }

    public function test_07_filter_produk_berdasarkan_kategori(): void
    {
        ['vendor' => $vendor] = $this->vendorActor();
        $categoryA = Category::factory()->create(['name' => 'Makanan']);
        $categoryB = Category::factory()->create(['name' => 'Kerajinan']);
        ['product' => $matched] = $this->productFor($vendor, ['category_id' => $categoryA->id]);
        $this->productFor($vendor, ['category_id' => $categoryB->id]);

        Livewire::test(Cari::class)
            ->set('selectedCategorySlug', $categoryA->slug)
            ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$matched->id]);
    }

    public function test_08_filter_produk_berdasarkan_harga(): void
    {
        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $expensive] = $this->productFor($vendor, ['name' => 'Produk Mahal'], ['price' => 300_000]);
        ['product' => $cheap] = $this->productFor($vendor, ['name' => 'Produk Murah'], ['price' => 50_000]);

        Livewire::test(Cari::class)
            ->set('selectedHarga', 'low_to_high')
            ->assertViewHas(
                'products',
                fn ($products) => $products->pluck('id')->all() === [$cheap->id, $expensive->id],
            );
    }

    public function test_09_detail_produk_menampilkan_informasi_produk(): void
    {
        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $product, 'variant' => $variant] = $this->productFor($vendor, [
            'name' => 'Madu Hutan Asli',
            'description' => 'Madu pilihan dari desa.',
        ], [
            'variant_name' => 'Botol 500 ml',
        ]);

        Livewire::test(Detail::class, ['slug' => $product->slug])
            ->assertOk()
            ->assertSee('Madu Hutan Asli')
            ->assertSee('Botol 500 ml')
            ->assertSet('selectedVariantId', $variant->id);
    }

    public function test_10_produk_varian_dan_jumlah_ditambahkan_ke_keranjang(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $product, 'variant' => $variant] = $this->productFor($vendor);

        Livewire::actingAs($customer)
            ->test(Detail::class, ['slug' => $product->slug])
            ->set('selectedVariantId', $variant->id)
            ->set('quantity', 3)
            ->call('addToCart')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_11_perubahan_jumlah_memperbarui_keranjang_dan_total(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        ['variant' => $variant] = $this->productFor($vendor, [], ['price' => 25_000]);
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 25_000,
        ]);

        $component = Livewire::actingAs($customer)
            ->test(CartDetails::class)
            ->call('setQuantity', $item->id, 4)
            ->assertHasNoErrors();

        $this->assertSame(4, (int) $item->fresh()->quantity);
        $this->assertSame(100_000.0, (float) $component->get('subtotal'));
    }

    public function test_12_item_dapat_dihapus_dari_keranjang(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        ['variant' => $variant] = $this->productFor($vendor);
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        Livewire::actingAs($customer)
            ->test(CartDetails::class)
            ->call('removeItem', $item->id);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_13_checkout_data_lengkap_membuat_order(): void
    {
        $customer = $this->actor('customer', ['phone' => '081234567890']);
        ShipmentAddress::factory()->create(['user_id' => $customer->id]);
        ['vendor' => $vendor] = $this->vendorActor();
        ['variant' => $variant] = $this->productFor($vendor, [], ['price' => 80_000, 'stock' => 10]);
        $courier = ShipmentCourier::factory()->create(['price' => 20_000]);
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 80_000,
        ]);

        Livewire::actingAs($customer)
            ->test(CartSummary::class)
            ->set('name', $customer->name)
            ->set('email', $customer->email)
            ->set('phone', $customer->phone)
            ->set('selectedCouriers', [$vendor->id => $courier->id])
            ->call('checkout')
            ->assertHasNoErrors()
            ->assertRedirect();

        $order = Order::query()->where('user_id', $customer->id)->firstOrFail();
        $this->assertSame(180_000.0, (float) $order->total_amount);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'amount' => 180_000]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_14_checkout_data_tidak_lengkap_ditolak(): void
    {
        $customer = $this->actor('customer', ['phone' => '081234567890']);
        ['vendor' => $vendor] = $this->vendorActor();
        ['variant' => $variant] = $this->productFor($vendor);
        $courier = ShipmentCourier::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        Livewire::actingAs($customer)
            ->test(CartSummary::class)
            ->set('selectedCouriers', [$vendor->id => $courier->id])
            ->call('checkout')
            ->assertHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_15_checkout_multi_vendor_membagi_order_per_vendor(): void
    {
        $customer = $this->actor('customer', ['phone' => '081234567890']);
        ShipmentAddress::factory()->create(['user_id' => $customer->id]);
        ['vendor' => $vendorA] = $this->vendorActor();
        ['vendor' => $vendorB] = $this->vendorActor();
        ['variant' => $variantA] = $this->productFor($vendorA);
        ['variant' => $variantB] = $this->productFor($vendorB);
        $courier = ShipmentCourier::factory()->create(['price' => 10_000]);
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variantA->id,
            'quantity' => 1,
            'price' => 100_000,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variantB->id,
            'quantity' => 1,
            'price' => 100_000,
        ]);

        Livewire::actingAs($customer)
            ->test(CartSummary::class)
            ->set('name', $customer->name)
            ->set('email', $customer->email)
            ->set('phone', $customer->phone)
            ->set('selectedCouriers', [
                $vendorA->id => $courier->id,
                $vendorB->id => $courier->id,
            ])
            ->call('checkout')
            ->assertHasNoErrors();

        $order = Order::query()->where('user_id', $customer->id)->firstOrFail();
        $this->assertSame(2, $order->orderVendors()->count());
        $this->assertEqualsCanonicalizing(
            [$vendorA->id, $vendorB->id],
            $order->orderVendors()->pluck('vendor_id')->all(),
        );
    }

    public function test_16_pembayaran_memproses_dan_memperbarui_status(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Pending,
            OrderPaymentStatus::Pending,
        );

        Livewire::actingAs($customer)
            ->test(PaymentPage::class, ['orderId' => $fixture['order']->id])
            ->call('payWithMidtrans')
            ->assertSet('snapToken', 'test-snap-token')
            ->assertHasNoErrors();

        app(PaymentService::class)->processNotification([
            'order_id' => $fixture['order']->order_number,
            'transaction_id' => 'test-transaction',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'gross_amount' => 120_000,
        ]);

        $this->assertSame(PaymentStatus::Success, $fixture['payment']->fresh()->status);
        $this->assertSame(OrderPaymentStatus::Paid, $fixture['order']->fresh()->payment_status);
    }

    public function test_17_detail_order_menampilkan_status_pembayaran_terbaru(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($customer)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['order']->id])
            ->assertSee('Sudah Dibayar');
    }

    public function test_18_riwayat_pesanan_hanya_menampilkan_order_customer(): void
    {
        $customer = $this->actor('customer');
        $otherCustomer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $own = $this->orderFor($customer, $vendor);
        $other = $this->orderFor($otherCustomer, $vendor);

        Livewire::actingAs($customer)
            ->test(HistoryPage::class)
            ->assertSee($own['order']->order_number)
            ->assertDontSee($other['order']->order_number);
    }

    public function test_19_detail_pesanan_menampilkan_rincian_order(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor(['name' => 'Pemilik Toko'], ['store_name' => 'Toko Skenario']);
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($customer)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['order']->id])
            ->assertSee($fixture['order']->order_number)
            ->assertSee('Toko Skenario');
    }

    public function test_20_tracking_customer_menampilkan_status_pengiriman(): void
    {
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Shipped,
            OrderPaymentStatus::Paid,
            OrderVendorStatus::Shipped,
            ShipmentStatus::Shipped,
        );

        Livewire::actingAs($customer)
            ->test(TrackingPage::class)
            ->assertSee($fixture['order']->order_number)
            ->assertSee('TRK-TEST-001');
    }

    public function test_21_logout_customer_mengakhiri_sesi_dan_kembali_ke_login(): void
    {
        $customer = $this->actor('customer');

        $this->actingAs($customer)
            ->post(route('user.logout'))
            ->assertRedirect(route('user.login'));

        $this->assertGuest();
    }
}
