<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Models\ProductVariant;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Orders\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $order = $this->record;
        $orderVendors = $order->orderVendors();
        $orderItems = [];

        $orderItems = $orderVendors->with('orderItems')->get()->flatMap(function ($orderVendor) {
            return $orderVendor->orderItems->toArray();
        })->toArray();


        $shipment = $order->orderVendors()->with('shipment')->first()->shipment;

        $payment = $order->payment;

        $data = array_merge($data, [
            'items' => $orderItems,
            'user_id' => $order->user_id,
            'vendor_id' => $orderVendors->first()->vendor_id,
            'shipment_address_id' => $shipment->shipment_address_id ?? null,
            'shipment_courier_id' => $shipment->shipment_courier_id ?? null,
            'shipping_cost' => $shipment->shipping_cost ?? 0,
            'total_amount_display' => $order->total_amount,
            'payment_method' => $payment->payment_method ?? null,
            'payment_status' => $order->payment_status,
            'status' => $order->status,

        ]);

        // dd($data);
        return $data;
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {
            // Update order main data
            $record->update([
                'user_id' => $data['user_id'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
            ]);

            // Get or create order vendor
            $orderVendor = $record->orderVendors()->first();

            if (!$orderVendor) {
                $orderVendor = $record->orderVendors()->create([
                    'vendor_id' => $data['vendor_id'],
                    'subtotal' => 0,
                    'status' => \App\Enums\OrderVendorStatus::Pending,
                ]);
            }

            // Delete existing order items
            $orderVendor->orderItems()->delete();

            // Calculate subtotal and create new order items
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
            }

            // Update order vendor subtotal
            $orderVendor->update(['subtotal' => $subtotal, 'vendor_id' => $data['vendor_id']]);

            // Update or create shipment
            $shipment = $orderVendor->shipment;
            $shippingCost = $data['shipping_cost'] ?? 0;

            if ($shipment) {
                $shipment->update([
                    'shipment_address_id' => $data['shipment_address_id'],
                    'shipment_courier_id' => $data['shipment_courier_id'],
                    'shipping_cost' => $shippingCost,
                ]);
            } else {
                $orderVendor->shipment()->create([
                    'shipment_address_id' => $data['shipment_address_id'],
                    'shipment_courier_id' => $data['shipment_courier_id'],
                    'shipping_cost' => $shippingCost,
                    'status' => \App\Enums\ShipmentStatus::Pending,
                ]);
            }

            // Calculate total amount
            $totalAmount = $subtotal + $shippingCost;
            $record->update(['total_amount' => $totalAmount]);

            // Update payment
            $payment = $record->payment;
            if ($payment) {
                $payment->update([
                    'payment_method' => $data['payment_method'],
                    'amount' => $totalAmount,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $record;
    }
}
