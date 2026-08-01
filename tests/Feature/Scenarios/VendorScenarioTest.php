<?php

namespace Tests\Feature\Scenarios;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Enums\VendorWalletTransactionType;
use App\Livewire\User\Auth\Login;
use App\Livewire\User\Auth\Regist;
use App\Livewire\Vendor\Dashboard\BankAccountPage;
use App\Livewire\Vendor\Dashboard\OrderDetailPage;
use App\Livewire\Vendor\Dashboard\OrderPage;
use App\Livewire\Vendor\Dashboard\OverviewPage;
use App\Livewire\Vendor\Dashboard\ProductFormPage;
use App\Livewire\Vendor\Dashboard\ProductPage;
use App\Livewire\Vendor\Dashboard\TrackingPage;
use App\Livewire\Vendor\Dashboard\WalletPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorBankAccount;
use App\Models\VendorWallet;
use App\Models\VendorWalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsEcommerceScenarios;
use Tests\TestCase;

class VendorScenarioTest extends TestCase
{
    use BuildsEcommerceScenarios;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedScenarioRoles();
    }

    public function test_22_registrasi_vendor_dengan_data_lengkap(): void
    {
        Livewire::test(Regist::class)
            ->set('nama', 'Vendor Baru')
            ->set('email', 'vendor-baru@example.test')
            ->set('phone', '081234567890')
            ->set('password', 'password-aman')
            ->set('password_confirmation', 'password-aman')
            ->set('role', 'vendor')
            ->call('regist')
            ->assertHasNoErrors()
            ->assertRedirect(route('user.login'));

        $vendor = Vendor::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'vendor-baru@example.test'))
            ->firstOrFail();

        $this->assertSame('Vendor Baru', $vendor->store_name);
        $this->assertSame('vendor', $vendor->user->role?->name);
    }

    public function test_23_registrasi_vendor_dengan_data_kosong_ditolak(): void
    {
        Livewire::test(Regist::class)
            ->set('nama', '')
            ->set('email', '')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->set('role', 'vendor')
            ->call('regist')
            ->assertHasErrors(['nama', 'email', 'password', 'password_confirmation']);

        $this->assertDatabaseCount('vendors', 0);
    }

    public function test_24_login_vendor_valid_mengarah_ke_dashboard_vendor(): void
    {
        ['user' => $vendorUser] = $this->vendorActor([
            'email' => 'vendor-login@example.test',
            'password' => 'password-aman',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'vendor-login@example.test')
            ->set('password', 'password-aman')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('vendor.dashboard'));

        $this->assertAuthenticatedAs($vendorUser);
    }

    public function test_25_login_vendor_tidak_valid_ditolak(): void
    {
        $this->vendorActor([
            'email' => 'vendor-invalid@example.test',
            'password' => 'password-benar',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'vendor-invalid@example.test')
            ->set('password', 'password-salah')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_26_dashboard_vendor_menampilkan_ringkasan_data(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $this->productFor($vendor);

        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');

        Livewire::actingAs($vendorUser)
            ->test(OverviewPage::class)
            ->assertViewHas('stats', fn ($stats) => $stats['totalProducts'] === 1);
    }

    public function test_27_vendor_menambahkan_produk_baru(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $category = Category::factory()->create();

        Livewire::actingAs($vendorUser)
            ->test(ProductFormPage::class)
            ->set('name', 'Produk Vendor Baru')
            ->set('category_id', $category->id)
            ->set('description', 'Deskripsi produk baru')
            ->set('price', 75_000)
            ->set('weight', 500)
            ->set('status', 'active')
            ->set('variants', [[
                'id' => null,
                'variant_name' => 'Standar',
                'sku' => 'SKU-VENDOR-001',
                'price' => 75_000,
                'stock' => 15,
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->where('name', 'Produk Vendor Baru')->firstOrFail();
        $this->assertSame($vendor->id, $product->vendor_id);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'SKU-VENDOR-001',
            'stock' => 15,
        ]);
    }

    public function test_28_produk_vendor_dengan_data_kosong_ditolak(): void
    {
        ['user' => $vendorUser] = $this->vendorActor();

        Livewire::actingAs($vendorUser)
            ->test(ProductFormPage::class)
            ->call('save')
            ->assertHasErrors(['name', 'category_id', 'variants']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_29_vendor_mengubah_data_produk(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->productFor($vendor, ['name' => 'Nama Lama'], ['sku' => 'SKU-EDIT-001']);

        Livewire::actingAs($vendorUser)
            ->test(ProductFormPage::class, ['productId' => $fixture['product']->id])
            ->set('name', 'Nama Produk Diperbarui')
            ->set('variants.0.stock', 99)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nama Produk Diperbarui', $fixture['product']->fresh()->name);
        $this->assertSame(99, (int) $fixture['variant']->fresh()->stock);
    }

    public function test_30_vendor_menghapus_produk_tanpa_riwayat_order(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->productFor($vendor);

        Livewire::actingAs($vendorUser)
            ->test(ProductPage::class)
            ->call('deleteProduct', $fixture['product']->id);

        $this->assertDatabaseMissing('products', ['id' => $fixture['product']->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $fixture['variant']->id]);
    }

    public function test_31_daftar_produk_hanya_menampilkan_produk_vendor_terkait(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        ['vendor' => $otherVendor] = $this->vendorActor();
        $own = $this->productFor($vendor, ['name' => 'Produk Milik Saya']);
        $other = $this->productFor($otherVendor, ['name' => 'Produk Vendor Lain']);

        Livewire::actingAs($vendorUser)
            ->test(ProductPage::class)
            ->assertSee($own['product']->name)
            ->assertDontSee($other['product']->name);
    }

    public function test_32_vendor_menambah_dan_memperbarui_varian_produk(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->productFor($vendor, [], ['sku' => 'SKU-VARIAN-001']);

        $component = Livewire::actingAs($vendorUser)
            ->test(ProductFormPage::class, ['productId' => $fixture['product']->id])
            ->call('addVariant');

        $variants = $component->get('variants');
        $variants[0]['stock'] = 40;
        $variants[1] = [
            'id' => null,
            'variant_name' => 'Varian Kedua',
            'sku' => 'SKU-VARIAN-002',
            'price' => 125_000,
            'stock' => 12,
        ];

        $component
            ->set('variants', $variants)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, $fixture['product']->productVariants()->count());
        $this->assertSame(40, (int) $fixture['variant']->fresh()->stock);
        $this->assertDatabaseHas('product_variants', ['sku' => 'SKU-VARIAN-002']);
    }

    public function test_33_vendor_melihat_daftar_order_masuk_miliknya(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        ['vendor' => $otherVendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $own = $this->orderFor($customer, $vendor);
        $other = $this->orderFor($customer, $otherVendor);

        Livewire::actingAs($vendorUser)
            ->test(OrderPage::class)
            ->assertSee($own['order']->order_number)
            ->assertDontSee($other['order']->order_number);
    }

    public function test_34_vendor_melihat_detail_order(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor([], ['store_name' => 'Toko Detail']);
        $customer = $this->actor('customer', ['name' => 'Customer Detail']);
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($vendorUser)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['orderVendor']->id])
            ->assertSee($fixture['order']->order_number)
            ->assertSee('Customer Detail');
    }

    public function test_35_vendor_memproses_order_yang_sudah_dibayar(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $fixture = $this->orderFor($customer, $vendor);
        $unpaidFixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Pending,
            OrderPaymentStatus::Pending,
        );

        Livewire::actingAs($vendorUser)
            ->test(OrderPage::class)
            ->call('processOrder', $fixture['orderVendor']->id)
            ->call('processOrder', $unpaidFixture['orderVendor']->id)
            ->assertSee('Pesanan tidak ditemukan atau tidak bisa diproses.');

        $this->assertSame(OrderVendorStatus::Processed, $fixture['orderVendor']->fresh()->status);
        $this->assertSame(OrderVendorStatus::Pending, $unpaidFixture['orderVendor']->fresh()->status);
    }

    public function test_35a_vendor_tetap_bisa_memproses_order_jika_payment_berhasil_tapi_status_order_belum_sinkron(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Paid,
            OrderPaymentStatus::Pending,
        );

        $fixture['payment']->update(['status' => PaymentStatus::Success]);

        Livewire::actingAs($vendorUser)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['orderVendor']->id])
            ->call('processOrder')
            ->assertHasNoErrors()
            ->assertSee('Pesanan berhasil diproses.');

        $this->assertSame(OrderVendorStatus::Processed, $fixture['orderVendor']->fresh()->status);
    }

    public function test_35b_vendor_tidak_melihat_tombol_proses_untuk_order_belum_dibayar(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Pending,
            OrderPaymentStatus::Pending,
        );

        Livewire::actingAs($vendorUser)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['orderVendor']->id])
            ->assertDontSee('Proses Pesanan')
            ->assertSee('Pesanan belum bisa diproses karena pembayaran masih menunggu konfirmasi.');
    }

    public function test_36_vendor_mengisi_data_pengiriman_dan_nomor_resi(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Paid,
            OrderPaymentStatus::Paid,
            OrderVendorStatus::Processed,
        );

        Livewire::actingAs($vendorUser)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['orderVendor']->id])
            ->set('tracking_number', 'RESI-VENDOR-001')
            ->call('shipOrder')
            ->assertHasNoErrors();

        $shipment = $fixture['shipment']->fresh();
        $this->assertSame('RESI-VENDOR-001', $shipment->tracking_number);
        $this->assertSame(ShipmentStatus::Shipped, $shipment->status);
    }

    public function test_37_vendor_mengubah_status_shipment_menjadi_tiba(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Shipped,
            OrderPaymentStatus::Paid,
            OrderVendorStatus::Shipped,
            ShipmentStatus::Shipped,
        );

        Livewire::actingAs($vendorUser)
            ->test(OrderDetailPage::class, ['orderId' => $fixture['orderVendor']->id])
            ->call('confirmDelivery');

        $this->assertSame(ShipmentStatus::Delivered, $fixture['shipment']->fresh()->status);
        $this->assertSame(OrderVendorStatus::Delivered, $fixture['orderVendor']->fresh()->status);
    }

    public function test_38_vendor_melihat_tracking_pengiriman(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Shipped,
            OrderPaymentStatus::Paid,
            OrderVendorStatus::Shipped,
            ShipmentStatus::Shipped,
        );

        Livewire::actingAs($vendorUser)
            ->test(TrackingPage::class)
            ->assertSee($fixture['order']->order_number)
            ->assertSee('TRK-TEST-001');
    }

    public function test_39_wallet_vendor_menampilkan_saldo_dan_riwayat_transaksi(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $wallet = VendorWallet::factory()->create([
            'vendor_id' => $vendor->id,
            'balance' => 250_000,
        ]);
        $transaction = VendorWalletTransaction::factory()->create([
            'vendor_wallet_id' => $wallet->id,
            'type' => VendorWalletTransactionType::Credit,
            'amount' => 250_000,
            'description' => 'Pencairan order pengujian',
        ]);

        Livewire::actingAs($vendorUser)
            ->test(WalletPage::class)
            ->assertViewHas('wallet', fn ($viewWallet) => $viewWallet->is($wallet))
            ->assertSee($transaction->description);
    }

    public function test_40_vendor_menambah_dan_mengubah_rekening(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();

        $component = Livewire::actingAs($vendorUser)
            ->test(BankAccountPage::class)
            ->set('bank_name', 'BRI')
            ->set('account_number', '1234567890')
            ->set('account_holder', 'Vendor Skenario')
            ->call('save')
            ->assertHasNoErrors();

        $account = VendorBankAccount::query()->where('vendor_id', $vendor->id)->firstOrFail();

        $component
            ->call('edit', $account->id)
            ->set('bank_name', 'BNI')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('BNI', $account->fresh()->bank_name);
    }

    public function test_41_vendor_melihat_laporan_order_pdf(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $this->orderFor($customer, $vendor);

        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.report.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_42_logout_vendor_mengakhiri_sesi(): void
    {
        ['user' => $vendorUser] = $this->vendorActor();

        $this->actingAs($vendorUser)
            ->post(route('user.logout'))
            ->assertRedirect(route('user.login'));

        $this->assertGuest();
    }
}
