<?php

namespace Tests\Feature;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Livewire\User\Dashboard\OrderDetailPage;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderDisbursementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_confirmation_credits_vendor_wallet_only_once(): void
    {
        PlatformSetting::query()->create(['admin_fee_percentage' => 10]);

        $customer = User::factory()->create();
        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $vendorUser->id]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => OrderStatus::Shipped,
            'payment_status' => OrderPaymentStatus::Paid,
        ]);
        $orderVendor = OrderVendor::query()->create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 100,
            'status' => OrderVendorStatus::Delivered,
        ]);

        Livewire::actingAs($customer)
            ->test(OrderDetailPage::class, ['orderId' => $order->id])
            ->call('confirmReceived', $orderVendor->id)
            ->call('confirmReceived', $orderVendor->id);

        $wallet = $vendor->vendorWallet()->firstOrFail();

        $this->assertSame(90.0, (float) $wallet->balance);
        $this->assertSame(1, $wallet->transactions()->count());
        $this->assertTrue($orderVendor->fresh()->is_disbursed);
        $this->assertSame(OrderVendorStatus::Completed, $orderVendor->fresh()->status);
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
    }
}
