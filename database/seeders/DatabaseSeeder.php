<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Vendor;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shipment;
use App\Models\UserRole;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\VendorWallet;
use App\Models\VendorAddress;
use App\Models\ProductVariant;
use App\Models\ShipmentAddress;
use Illuminate\Database\Seeder;
use App\Models\VendorBankAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('admin'),
                'status' => 'active'
            ],
            [
                'name' => 'Vendor One',
                'email' => 'vendor@gmail.com',
                'password' => bcrypt('vendor'),
                'status' => 'active'
            ],
            [
                'name' => 'Customer One',
                'email' => 'customer@gmail.com',
                'password' => bcrypt('customer'),
                'status' => 'active'
            ],
        ]);

        Role::insert([
            ['name' => 'Admin'],
            ['name' => 'Vendor'],
            ['name' => 'Customer'],
        ]);

        UserRole::insert([
            ['user_id' => 1, 'role_id' => 1],
            ['user_id' => 2, 'role_id' => 2],
            ['user_id' => 3, 'role_id' => 3],
        ]);

        Vendor::insert([
            [
                'user_id' => 2,
                'store_name' => 'Toko Elektronik',
                'slug' => 'toko-elektronik',
                'status' => 'active'
            ],
            [
                'user_id' => 2,
                'store_name' => 'Toko Fashion',
                'slug' => 'toko-fashion',
                'status' => 'active'
            ],
            [
                'user_id' => 2,
                'store_name' => 'Toko Buku',
                'slug' => 'toko-buku',
                'status' => 'active'
            ],
        ]);

        VendorAddress::insert([
            ['vendor_id' => 1, 'province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Cicendo', 'postal_code' => '40173', 'address' => 'Jl. A'],
            ['vendor_id' => 2, 'province' => 'DKI Jakarta', 'city' => 'Jakarta', 'district' => 'Setiabudi', 'postal_code' => '12910', 'address' => 'Jl. B'],
            ['vendor_id' => 3, 'province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Wonokromo', 'postal_code' => '60243', 'address' => 'Jl. C'],
        ]);

        VendorBankAccount::insert([
            ['vendor_id' => 1, 'bank_name' => 'BCA', 'account_number' => '123456', 'account_holder' => 'Vendor A'],
            ['vendor_id' => 2, 'bank_name' => 'BRI', 'account_number' => '234567', 'account_holder' => 'Vendor B'],
            ['vendor_id' => 3, 'bank_name' => 'BNI', 'account_number' => '345678', 'account_holder' => 'Vendor C'],
        ]);

        Category::insert([
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Fashion', 'slug' => 'fashion'],
            ['name' => 'Buku', 'slug' => 'buku'],
        ]);

        Product::insert([
            ['vendor_id' => 1, 'category_id' => 1, 'name' => 'Laptop', 'slug' => 'laptop', 'price' => 10000000],
            ['vendor_id' => 2, 'category_id' => 2, 'name' => 'Kaos', 'slug' => 'kaos', 'price' => 100000],
            ['vendor_id' => 3, 'category_id' => 3, 'name' => 'Novel', 'slug' => 'novel', 'price' => 80000],
        ]);

        ProductVariant::insert([
            ['product_id' => 1, 'sku' => 'LAP-01', 'variant_name' => 'Default', 'price' => 10000000, 'stock' => 10],
            ['product_id' => 2, 'sku' => 'KAO-01', 'variant_name' => 'L', 'price' => 100000, 'stock' => 20],
            ['product_id' => 3, 'sku' => 'BUK-01', 'variant_name' => 'Softcover', 'price' => 80000, 'stock' => 15],
        ]);

        Order::insert([
            ['user_id' => 3, 'order_number' => 'ORD001', 'total_amount' => 10000000],
            ['user_id' => 3, 'order_number' => 'ORD002', 'total_amount' => 100000],
            ['user_id' => 3, 'order_number' => 'ORD003', 'total_amount' => 80000],
        ]);

        Payment::insert([
            ['order_id' => 1, 'payment_method' => 'transfer', 'amount' => 10000000, 'status' => 'success'],
            ['order_id' => 2, 'payment_method' => 'ewallet', 'amount' => 100000, 'status' => 'success'],
            ['order_id' => 3, 'payment_method' => 'cod', 'amount' => 80000, 'status' => 'pending'],
        ]);

        VendorWallet::insert([
            ['vendor_id' => 1, 'balance' => 5000000],
            ['vendor_id' => 2, 'balance' => 2000000],
            ['vendor_id' => 3, 'balance' => 1000000],
        ]);

        OrderVendor::insert([
            ['order_id' => 1, 'vendor_id' => 1, 'subtotal' => 10000000],
            ['order_id' => 2, 'vendor_id' => 2, 'subtotal' => 100000],
            ['order_id' => 3, 'vendor_id' => 3, 'subtotal' => 80000],
        ]);

        Shipment::insert([
            ['order_vendor_id' => 1,  'courier' => 'JNE', 'service' => 'REG', 'shipping_cost' => 20000],
            ['order_vendor_id' => 2, 'courier' => 'J&T', 'service' => 'EZ', 'shipping_cost' => 15000],
            ['order_vendor_id' => 3, 'courier' => 'SiCepat', 'service' => 'BEST', 'shipping_cost' => 18000],
        ]);

        ShipmentAddress::insert([
            ['user_id' => 3, 'shipment_id' => 1, 'province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Cicendo', 'postal_code' => '40173', 'address' => 'Jl. Pelanggan 1'],
            ['user_id' => 3, 'shipment_id' => 2, 'province' => 'DKI Jakarta', 'city' => 'Jakarta', 'district' => 'Setiabudi', 'postal_code' => '12910', 'address' => 'Jl. Pelanggan 2'],
            ['user_id' => 3, 'shipment_id' => 3, 'province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Wonokromo', 'postal_code' => '60243', 'address' => 'Jl. Pelanggan 3'],
        ]);

        OrderItem::insert([
            ['order_vendor_id' => 1, 'product_variant_id' => 1, 'price' => 10000000, 'quantity' => 1, 'total' => 10000000],
            ['order_vendor_id' => 2, 'product_variant_id' => 2, 'price' => 100000, 'quantity' => 1, 'total' => 100000],
            ['order_vendor_id' => 3, 'product_variant_id' => 3, 'price' => 80000, 'quantity' => 1, 'total' => 80000],
        ]);

        Review::insert([
            ['user_id' => 3, 'product_id' => 1, 'order_item_id' => 1, 'rating' => 5],
            ['user_id' => 3, 'product_id' => 2, 'order_item_id' => 2, 'rating' => 4],
            ['user_id' => 3, 'product_id' => 3, 'order_item_id' => 3, 'rating' => 5],
        ]);
    }
}
