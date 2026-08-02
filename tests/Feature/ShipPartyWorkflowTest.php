<?php

namespace Tests\Feature;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\ShipmentStatus;
use App\Livewire\ShipParty\Dashboard\OrderDetailPage as ShipPartyOrderDetailPage;
use App\Enums\UserStatus;
use App\Livewire\Vendor\Dashboard\OrderDetailPage;
use App\Models\Role;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsEcommerceScenarios;
use Tests\TestCase;

class ShipPartyWorkflowTest extends TestCase
{
    use BuildsEcommerceScenarios;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedScenarioRoles();
    }

    public function test_vendor_process_order_menunggu_ship_party_input_resi(): void
    {
        ['user' => $vendorUser, 'vendor' => $vendor] = $this->vendorActor();
        $customer = $this->actor('customer');
        $shipPartyUser = User::factory()->create(['status' => UserStatus::Active]);

        UserRole::query()->create([
            'user_id' => $shipPartyUser->id,
            'role_id' => Role::query()->where('name', 'pihak_kapal')->value('id'),
        ]);

        $orderData = $this->orderFor(
            $customer,
            $vendor,
            OrderStatus::Paid,
            OrderPaymentStatus::Paid,
            OrderVendorStatus::Pending,
            ShipmentStatus::Pending,
        );

        $orderData['courier']->update(['user_id' => $shipPartyUser->id]);

        Livewire::actingAs($vendorUser)
            ->test(OrderDetailPage::class, ['orderId' => $orderData['orderVendor']->id])
            ->call('processOrder');

        $this->assertSame(OrderVendorStatus::Processed, $orderData['orderVendor']->fresh()->status);
        $this->assertSame(ShipmentStatus::Pending, $orderData['shipment']->fresh()->status);
        $this->assertNull($orderData['shipment']->fresh()->tracking_number);
        $this->assertNull($orderData['shipment']->fresh()->shipped_at);

        Livewire::actingAs($shipPartyUser)
            ->test(ShipPartyOrderDetailPage::class, ['orderId' => $orderData['orderVendor']->id])
            ->set('tracking_number', 'KPL-INPUT-001')
            ->call('shipOrder');

        $this->assertSame(OrderVendorStatus::Shipped, $orderData['orderVendor']->fresh()->status);
        $this->assertSame(ShipmentStatus::Shipped, $orderData['shipment']->fresh()->status);
        $this->assertSame('KPL-INPUT-001', $orderData['shipment']->fresh()->tracking_number);
        $this->assertNotNull($orderData['shipment']->fresh()->shipped_at);
    }

    public function test_ship_party_user_can_access_dashboard_for_assigned_courier(): void
    {
        $shipPartyUser = User::factory()->create(['status' => UserStatus::Active]);

        UserRole::query()->create([
            'user_id' => $shipPartyUser->id,
            'role_id' => Role::query()->where('name', 'pihak_kapal')->value('id'),
        ]);

        ShipmentCourier::factory()->create(['user_id' => $shipPartyUser->id]);

        $this->actingAs($shipPartyUser)
            ->get(route('ship-party.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pihak Kapal');
    }
}
