<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Models\ProductVariant;
use App\Services\AdminFeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Orders\OrderResource;

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
                'status' => \App\Enums\OrderVendorStatus::Pending,
            ]);

            $adminFeeService = app(AdminFeeService::class);

            // Calculate subtotal and create order items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $variant = ProductVariant::find($item['product_variant_id']);
                $itemTotal = $variant->price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderVendor->orderItems()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $variant->price,
                    'total' => $itemTotal,
                ]);

                // Reduce stock
                $variant->decrement('stock', $item['quantity']);
            }

            // Update order vendor subtotal
            $orderVendor->update(['subtotal' => $subtotal]);
            $adminFeeService->syncOrderVendor($orderVendor);

            // Create shipment
            $shippingCost = $data['shipping_cost'] ?? 0;
            $orderVendor->shipment()->create([
                'shipment_address_id' => $data['shipment_address_id'],
                'shipment_courier_id' => $data['shipment_courier_id'],
                'shipping_cost' => $shippingCost,
                'status' => \App\Enums\ShipmentStatus::Pending,
            ]);

            // Calculate total amount
            $totalAmount = $subtotal + $shippingCost;
            $order->update(['total_amount' => $totalAmount]);

            // Create payment
            $order->payment()->create([
                'payment_method' => $data['payment_method'],
                'amount' => $totalAmount,
                'status' => \App\Enums\PaymentStatus::Pending,
            ]);

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
