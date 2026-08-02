<?php

namespace Tests\Feature\Scenarios;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\ProductStatus;
use App\Filament\Pages\OrderReport;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\ShipParties\Pages\CreateShipParty;
use App\Filament\Resources\ShipParties\Pages\EditShipParty;
use App\Filament\Resources\ShipParties\Pages\ListShipParties;
use App\Filament\Resources\Shipments\Pages\ListShipments;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Resources\Vendors\Pages\EditVendor;
use App\Filament\Resources\Vendors\Pages\ListVendors;
use App\Filament\Widgets\AdminStatsOverview;
use App\Livewire\User\Auth\Login;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\Role;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorWallet;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\BuildsEcommerceScenarios;
use Tests\TestCase;

class AdminScenarioTest extends TestCase
{
    use BuildsEcommerceScenarios;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedScenarioRoles();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_43_login_admin_valid_mengarah_ke_dashboard_admin(): void
    {
        $admin = $this->actor('admin', [
            'email' => 'admin-login@example.test',
            'password' => 'password-aman',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin-login@example.test')
            ->set('password', 'password-aman')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_44_login_admin_tidak_valid_ditolak(): void
    {
        $this->actor('admin', [
            'email' => 'admin-invalid@example.test',
            'password' => 'password-benar',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin-invalid@example.test')
            ->set('password', 'password-salah')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_45_dashboard_admin_dapat_diakses_dan_menampilkan_ringkasan(): void
    {
        $admin = $this->actor('admin');

        $this->actingAs($admin)
            ->get(route('filament.admin.pages.dashboard'))
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(AdminStatsOverview::class)
            ->assertSee('Ringkasan Marketplace')
            ->assertSee('Total Order');
    }

    public function test_46_admin_melihat_data_customer(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer', ['name' => 'Customer Daftar Admin']);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$customer]);
    }

    public function test_47_admin_menambah_data_customer_dengan_role_dan_password_terenkripsi(): void
    {
        $admin = $this->actor('admin');
        $customerRole = Role::query()->where('name', 'customer')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Customer Buatan Admin',
                'email' => 'customer-admin@example.test',
                'phone' => '081234567890',
                'password' => 'password-aman',
                'role_id' => $customerRole->id,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $customer = User::query()->where('email', 'customer-admin@example.test')->firstOrFail();
        $this->assertSame('customer', $customer->role?->name);
        $this->assertTrue(Hash::check('password-aman', $customer->password));
    }

    public function test_48_admin_mengubah_data_customer(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer', [
            'name' => 'Nama Customer Lama',
            'phone' => '081234567890',
        ]);
        $customerRole = Role::query()->where('name', 'customer')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $customer->getRouteKey()])
            ->fillForm(fn (array $state) => array_merge($state, [
                'name' => 'Nama Customer Baru',
                'role_id' => $customerRole->id,
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nama Customer Baru', $customer->fresh()->name);
        $this->assertSame('customer', $customer->fresh()->role?->name);
    }

    public function test_49_admin_menghapus_data_customer(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $customer->getRouteKey()])
            ->callAction('delete')
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }

    public function test_50_admin_melihat_data_vendor(): void
    {
        $admin = $this->actor('admin');
        ['vendor' => $vendor] = $this->vendorActor([], ['store_name' => 'Vendor Daftar Admin']);

        Livewire::actingAs($admin)
            ->test(ListVendors::class)
            ->assertCanSeeTableRecords([$vendor]);
    }

    public function test_51_admin_menambah_data_vendor_dan_menetapkan_role_vendor(): void
    {
        Storage::fake('public');
        $admin = $this->actor('admin');
        $prospectiveVendor = $this->actor('customer');

        Livewire::actingAs($admin)
            ->test(CreateVendor::class)
            ->fillForm([
                'user_id' => $prospectiveVendor->id,
                'store_name' => 'Vendor Buatan Admin',
                'description' => 'Vendor untuk pengujian',
                'logo' => UploadedFile::fake()->image('logo.jpg'),
                'banner' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
                'is_verified' => true,
                'rating' => 4.5,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $vendor = Vendor::query()->where('user_id', $prospectiveVendor->id)->firstOrFail();
        $this->assertSame('Vendor Buatan Admin', $vendor->store_name);
        $this->assertSame('vendor', $prospectiveVendor->fresh()->role?->name);
    }

    public function test_52_admin_mengubah_data_vendor(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/logo-lama.jpg', 'logo');
        Storage::disk('public')->put('branding/banner-lama.jpg', 'banner');

        $admin = $this->actor('admin');
        ['vendor' => $vendor] = $this->vendorActor([], [
            'store_name' => 'Nama Vendor Lama',
            'logo' => 'branding/logo-lama.jpg',
            'banner' => 'branding/banner-lama.jpg',
        ]);

        Livewire::actingAs($admin)
            ->test(EditVendor::class, ['record' => $vendor->getRouteKey()])
            ->fillForm(fn (array $state) => array_merge($state, [
                'store_name' => 'Nama Vendor Baru',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nama Vendor Baru', $vendor->fresh()->store_name);
    }

    public function test_53_admin_menghapus_data_vendor(): void
    {
        $admin = $this->actor('admin');
        ['vendor' => $vendor] = $this->vendorActor();

        Livewire::actingAs($admin)
            ->test(EditVendor::class, ['record' => $vendor->getRouteKey()])
            ->callAction('delete')
            ->assertRedirect();

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    public function test_54_admin_melihat_data_produk(): void
    {
        $admin = $this->actor('admin');
        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $product] = $this->productFor($vendor, ['name' => 'Produk Daftar Admin']);

        Livewire::actingAs($admin)
            ->test(ListProducts::class)
            ->assertCanSeeTableRecords([$product]);
    }

    public function test_54a_admin_melihat_data_pihak_kapal(): void
    {
        $admin = $this->actor('admin');
        $shipParty = $this->actor('pihak_kapal', ['name' => 'Pihak Kapal Admin']);

        Livewire::actingAs($admin)
            ->test(ListShipParties::class)
            ->assertCanSeeTableRecords([$shipParty]);
    }

    public function test_54b_admin_menambah_pihak_kapal_dan_mengaitkan_ke_ekspedisi(): void
    {
        $admin = $this->actor('admin');
        $courier = ShipmentCourier::factory()->create([
            'name' => 'Pelni',
            'code' => 'pelni',
            'service' => 'Kapal Cepat',
            'user_id' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(CreateShipParty::class)
            ->fillForm([
                'name' => 'Akun Pihak Kapal',
                'email' => 'ship-party-admin@example.test',
                'phone' => '081234567899',
                'password' => 'password-aman',
                'status' => 'active',
                'shipment_courier_id' => $courier->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $shipParty = User::query()->where('email', 'ship-party-admin@example.test')->firstOrFail();
        $this->assertSame('pihak_kapal', $shipParty->role?->name);
        $this->assertSame($shipParty->id, $courier->fresh()->user_id);
    }

    public function test_54c_admin_mengubah_pihak_kapal_dan_memindahkan_kaitan_ekspedisi(): void
    {
        $admin = $this->actor('admin');
        $shipParty = $this->actor('pihak_kapal', [
            'name' => 'Pihak Kapal Lama',
            'phone' => '081234567891',
        ]);
        $oldCourier = ShipmentCourier::factory()->create([
            'name' => 'Pelni Lama',
            'code' => 'pelni-lama',
            'service' => 'Reguler',
            'user_id' => $shipParty->id,
        ]);
        $newCourier = ShipmentCourier::factory()->create([
            'name' => 'Pelni Baru',
            'code' => 'pelni-baru',
            'service' => 'Express',
            'user_id' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditShipParty::class, ['record' => $shipParty->getRouteKey()])
            ->fillForm(fn (array $state) => array_merge($state, [
                'name' => 'Pihak Kapal Baru',
                'shipment_courier_id' => $newCourier->id,
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Pihak Kapal Baru', $shipParty->fresh()->name);
        $this->assertNull($oldCourier->fresh()->user_id);
        $this->assertSame($shipParty->id, $newCourier->fresh()->user_id);
        $this->assertSame('pihak_kapal', $shipParty->fresh()->role?->name);
    }

    public function test_55_admin_menambah_mengubah_dan_menghapus_kategori(): void
    {
        $admin = $this->actor('admin');

        Livewire::actingAs($admin)
            ->test(CreateCategory::class)
            ->fillForm([
                'name' => 'Kategori Admin',
                'subCategory' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::query()->where('name', 'Kategori Admin')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(fn (array $state) => array_merge($state, ['name' => 'Kategori Diperbarui']))
            ->call('save')
            ->assertHasNoFormErrors()
            ->callAction('delete');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_56_admin_mengubah_dan_menghapus_produk(): void
    {
        $admin = $this->actor('admin');
        ['vendor' => $vendor] = $this->vendorActor();
        ['product' => $product] = $this->productFor($vendor, ['name' => 'Produk Lama']);

        $component = Livewire::actingAs($admin)
            ->test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(fn (array $state) => array_merge($state, [
                'name' => 'Produk Admin Diperbarui',
                'status' => ProductStatus::Draft->value,
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Produk Admin Diperbarui', $product->fresh()->name);

        $component->callAction('delete');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_57_admin_melihat_data_order(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->assertCanSeeTableRecords([$fixture['order']]);
    }

    public function test_58_admin_melihat_detail_transaksi_order(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($admin)
            ->test(EditOrder::class, ['record' => $fixture['order']->getRouteKey()])
            ->assertOk()
            ->assertSee($fixture['order']->order_number);

        $this->actingAs($admin)
            ->get(OrderResource::getUrl('edit', ['record' => $fixture['order']]))
            ->assertOk();
    }

    public function test_59_admin_memantau_data_pembayaran(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->assertCanSeeTableRecords([$fixture['order']])
            ->assertSee('Midtrans')
            ->assertSee('Sudah Dibayar');
    }

    public function test_60_admin_melihat_data_shipment(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($admin)
            ->test(ListShipments::class)
            ->assertCanSeeTableRecords([$fixture['shipment']]);
    }

    public function test_61_admin_melihat_laporan_order(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        $fixture = $this->orderFor($customer, $vendor);

        Livewire::actingAs($admin)
            ->test(OrderReport::class)
            ->assertSee($fixture['order']->order_number)
            ->assertSee('Total Order');
    }

    public function test_62_admin_mencairkan_payment_vendor(): void
    {
        $admin = $this->actor('admin');
        $customer = $this->actor('customer');
        ['vendor' => $vendor] = $this->vendorActor();
        PlatformSetting::query()->create(['admin_fee_percentage' => 10]);
        $fixture = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Completed,
            OrderPaymentStatus::Paid,
            OrderVendorStatus::Completed,
        );

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->callTableAction('disburse_to_vendors', $fixture['order'])
            ->assertHasNoErrors();

        $wallet = VendorWallet::query()->where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame(90_000.0, (float) $wallet->balance);
        $this->assertTrue($fixture['orderVendor']->fresh()->is_disbursed);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_63_logout_admin_mengakhiri_sesi(): void
    {
        $admin = $this->actor('admin');

        $this->actingAs($admin)
            ->post(route('filament.admin.auth.logout'))
            ->assertRedirect(route('filament.admin.auth.login'));

        $this->assertGuest();
    }
}
