<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\ProductVariant;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Services\AdminFeeService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();
        try {

            // Create order
            $order = static::getModel()::create([
                'user_id' => $data['user_id'],
                'total_amount' => 0,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
            ]);

            // dd($data);
            // Create order vendor
            $orderVendor = $order->orderVendors()->create([
                'vendor_id' => $data['vendor_id'],
                'subtotal' => 0,
                'status' => OrderVendorStatus::Pending,
            ]);

            $adminFeeService = app(AdminFeeService::class);

            // Calculate subtotal and create order items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $variant = ProductVariant::query()
                    ->whereKey($item['product_variant_id'])
                    ->whereHas('product', fn ($query) => $query->where('vendor_id', $data['vendor_id']))
                    ->lockForUpdate()
                    ->first();
                $quantity = (int) $item['quantity'];

                if (! $variant || $quantity < 1 || $quantity > (int) $variant->stock) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tidak valid atau stok tidak mencukupi.',
                    ]);
                }

                $itemTotal = $variant->price * $quantity;
                $subtotal += $itemTotal;

                $orderVendor->orderItems()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $quantity,
                    'price' => $variant->price,
                    'total' => $itemTotal,
                ]);

                // Reduce stock
                $variant->decrement('stock', $quantity);
            }

            // Update order vendor subtotal
            $orderVendor->update(['subtotal' => $subtotal]);
            $adminFeeService->syncOrderVendor($orderVendor);

            // Create shipment
            $validAddress = ShipmentAddress::query()
                ->whereKey($data['shipment_address_id'])
                ->where('user_id', $data['user_id'])
                ->exists();
            $courier = ShipmentCourier::query()->find($data['shipment_courier_id']);

            if (! $validAddress || ! $courier) {
                throw ValidationException::withMessages([
                    'shipment_address_id' => 'Alamat atau kurir pengiriman tidak valid.',
                ]);
            }

            $shippingCost = (float) $courier->price;
            $orderVendor->shipment()->create([
                'shipment_address_id' => $data['shipment_address_id'],
                'shipment_courier_id' => $data['shipment_courier_id'],
                'shipping_cost' => $shippingCost,
                'status' => ShipmentStatus::Pending,
            ]);

            // Calculate total amount
            $totalAmount = $subtotal + $shippingCost;
            $order->update(['total_amount' => $totalAmount]);

            // Create payment
            $order->payment()->create([
                'payment_method' => $data['payment_method'],
                'amount' => $totalAmount,
                'status' => PaymentStatus::Pending,
            ]);

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
