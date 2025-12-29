<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Vendor;
use App\Models\VendorAddress;
use App\Models\VendorBankAccount;
use App\Models\VendorWallet;
use App\Models\VendorWalletTransaction;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Factory validation: buat 1 record per model (ringan), dengan relasi terhubung.

        $user = User::factory()->create();
        $role = Role::factory()->create();
        UserRole::factory()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        VendorAddress::factory()->create(['vendor_id' => $vendor->id]);
        VendorBankAccount::factory()->create(['vendor_id' => $vendor->id]);

        $vendorWallet = VendorWallet::factory()->create(['vendor_id' => $vendor->id]);
        VendorWalletTransaction::factory()->create(['vendor_wallet_id' => $vendorWallet->id]);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
        ]);
        ProductImage::factory()->create(['product_id' => $product->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $order = Order::factory()->create(['user_id' => $user->id]);
        $orderVendor = OrderVendor::factory()->create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_vendor_id' => $orderVendor->id,
            'product_variant_id' => $variant->id,
        ]);
        Payment::factory()->create(['order_id' => $order->id]);

        $courier = ShipmentCourier::factory()->create();
        $shipmentAddress = ShipmentAddress::factory()->create(['user_id' => $user->id]);
        Shipment::factory()->create([
            'order_vendor_id' => $orderVendor->id,
            'shipment_address_id' => $shipmentAddress->id,
            'shipment_courier_id' => $courier->id,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => $orderItem->id,
        ]);
    }
}
