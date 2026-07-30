<?php

namespace Tests\Concerns;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserStatus;
use App\Enums\VendorStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Vendor;

trait BuildsEcommerceScenarios
{
    protected function seedScenarioRoles(): void
    {
        foreach (['customer', 'vendor', 'admin'] as $role) {
            Role::query()->firstOrCreate(['name' => $role]);
        }
    }

    protected function actor(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'status' => UserStatus::Active,
        ], $attributes));

        UserRole::query()->create([
            'user_id' => $user->id,
            'role_id' => Role::query()->where('name', $role)->value('id'),
        ]);

        return $user->refresh();
    }

    /**
     * @return array{user: User, vendor: Vendor}
     */
    protected function vendorActor(array $userAttributes = [], array $vendorAttributes = []): array
    {
        $user = $this->actor('vendor', $userAttributes);
        $vendor = Vendor::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => VendorStatus::Active,
        ], $vendorAttributes));

        return compact('user', 'vendor');
    }

    /**
     * @return array{category: Category, product: Product, variant: ProductVariant}
     */
    protected function productFor(Vendor $vendor, array $productAttributes = [], array $variantAttributes = []): array
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(array_merge([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
            'price' => 100_000,
        ], $productAttributes));
        $variant = ProductVariant::factory()->create(array_merge([
            'product_id' => $product->id,
            'price' => 100_000,
            'stock' => 20,
        ], $variantAttributes));

        return compact('category', 'product', 'variant');
    }

    /**
     * @return array{
     *     order: Order,
     *     orderVendor: OrderVendor,
     *     payment: Payment,
     *     shipment: Shipment,
     *     address: ShipmentAddress,
     *     courier: ShipmentCourier,
     *     variant: ProductVariant
     * }
     */
    protected function orderFor(
        User $customer,
        Vendor $vendor,
        OrderStatus $orderStatus = OrderStatus::Paid,
        OrderPaymentStatus $paymentStatus = OrderPaymentStatus::Paid,
        OrderVendorStatus $vendorStatus = OrderVendorStatus::Pending,
        ShipmentStatus $shipmentStatus = ShipmentStatus::Pending,
    ): array {
        $productData = $this->productFor($vendor);
        $address = ShipmentAddress::factory()->create(['user_id' => $customer->id]);
        $courier = ShipmentCourier::factory()->create(['price' => 20_000]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total_amount' => 120_000,
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
        ]);
        $orderVendor = OrderVendor::query()->create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 100_000,
            'status' => $vendorStatus,
        ]);
        OrderItem::query()->create([
            'order_vendor_id' => $orderVendor->id,
            'product_variant_id' => $productData['variant']->id,
            'quantity' => 1,
            'price' => 100_000,
            'total' => 100_000,
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'midtrans',
            'payment_gateway' => 'midtrans',
            'amount' => 120_000,
            'status' => $paymentStatus === OrderPaymentStatus::Paid
                ? PaymentStatus::Success
                : PaymentStatus::Pending,
        ]);
        $shipment = Shipment::query()->create([
            'order_vendor_id' => $orderVendor->id,
            'shipment_address_id' => $address->id,
            'shipment_courier_id' => $courier->id,
            'tracking_number' => $shipmentStatus === ShipmentStatus::Pending ? null : 'TRK-TEST-001',
            'shipping_cost' => 20_000,
            'status' => $shipmentStatus,
            'shipped_at' => $shipmentStatus === ShipmentStatus::Pending ? null : now(),
            'delivered_at' => $shipmentStatus === ShipmentStatus::Delivered ? now() : null,
        ]);

        return [
            'order' => $order,
            'orderVendor' => $orderVendor,
            'payment' => $payment,
            'shipment' => $shipment,
            'address' => $address,
            'courier' => $courier,
            'variant' => $productData['variant'],
        ];
    }
}
