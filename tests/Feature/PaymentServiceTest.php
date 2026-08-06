<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransfers\Payment\MidtransCallbackData;
use App\DataTransfers\Payment\SnapTransactionData;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_failed_notifications_restore_stock_only_once(): void
    {
        [$order, $variant] = $this->createPendingOrder();
        $service = new PaymentService(new FakePaymentGateway);
        $payload = $this->notificationPayload($order, 'expire');

        $service->processNotification($payload);
        $service->processNotification($payload);

        $this->assertSame(5, (int) $variant->fresh()->stock);
        $this->assertSame(PaymentStatus::Failed, $order->payment->fresh()->status);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_notification_with_wrong_amount_is_rejected_without_changing_state(): void
    {
        [$order, $variant] = $this->createPendingOrder();
        $service = new PaymentService(new FakePaymentGateway);
        $payload = $this->notificationPayload($order, 'settlement');
        $payload['gross_amount'] = 99;

        try {
            $service->processNotification($payload);
            $this->fail('A mismatched amount should be rejected.');
        } catch (\UnexpectedValueException) {
            // Expected.
        }

        $this->assertSame(3, (int) $variant->fresh()->stock);
        $this->assertSame(PaymentStatus::Pending, $order->payment->fresh()->status);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_notification_accepts_matching_midtrans_gross_amount_when_payment_amount_is_stale(): void
    {
        [$order] = $this->createPendingOrder(paymentAmount: 99);
        $service = new PaymentService(new FakePaymentGateway);

        $service->processNotification($this->notificationPayload($order, 'settlement'));

        $this->assertSame(PaymentStatus::Success, $order->payment->fresh()->status);
        $this->assertSame(OrderPaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_sync_status_accepts_matching_midtrans_gross_amount_when_payment_amount_is_stale(): void
    {
        [$order] = $this->createPendingOrder(paymentAmount: 99);
        $service = new PaymentService(new FakePaymentGateway([
            'transaction_id' => 'transaction-1',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'gross_amount' => 100,
        ]));

        $service->syncPaymentStatus($order);

        $this->assertSame(PaymentStatus::Success, $order->payment->fresh()->status);
        $this->assertSame(OrderPaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    /**
     * @return array{Order, ProductVariant}
     */
    private function createPendingOrder(float|int $paymentAmount = 100): array
    {
        $customer = User::factory()->create();
        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $vendorUser->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 50,
            'stock' => 3,
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => OrderStatus::Pending,
            'payment_status' => OrderPaymentStatus::Pending,
        ]);
        $orderVendor = OrderVendor::query()->create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 100,
            'status' => OrderVendorStatus::Pending,
        ]);
        OrderItem::query()->create([
            'order_vendor_id' => $orderVendor->id,
            'product_variant_id' => $variant->id,
            'price' => 50,
            'quantity' => 2,
            'total' => 100,
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'midtrans',
            'payment_gateway' => 'midtrans',
            'amount' => $paymentAmount,
            'status' => PaymentStatus::Pending,
        ]);

        return [$order->fresh('payment'), $variant];
    }

    private function notificationPayload(Order $order, string $status): array
    {
        return [
            'order_id' => $order->order_number,
            'transaction_id' => 'transaction-1',
            'transaction_status' => $status,
            'payment_type' => 'bank_transfer',
            'gross_amount' => 100,
        ];
    }
}

final class FakePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private array $statusPayload = [],
    ) {}

    public function createTransaction(SnapTransactionData $data): array
    {
        return ['token' => 'token', 'redirect_url' => 'https://example.test/payment'];
    }

    public function handleNotification(array $payload): MidtransCallbackData
    {
        return MidtransCallbackData::fromNotification($payload);
    }

    public function getTransactionStatus(string $orderId): MidtransCallbackData
    {
        return MidtransCallbackData::fromNotification(array_merge([
            'order_id' => $orderId,
        ], $this->statusPayload));
    }

    public function cancelTransaction(string $orderId): bool
    {
        return true;
    }
}
