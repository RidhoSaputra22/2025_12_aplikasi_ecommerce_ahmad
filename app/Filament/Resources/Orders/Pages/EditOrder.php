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
            'shipment_address_id' => $shipment->shipment_address_id,
            'shipment_courier_id' => $shipment->shipment_courier_id,
            'shipping_cost' => $shipment->shipping_cost,
            'total_amount_display' => $order->total_amount,
            'payment_method' => $payment->payment_method,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
        ]);



        return $data;
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {

            // dd($data);
            $orderItemsData = $data['items'];


            // Update order
            $record->update([
                'user_id' => $data['user_id'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
            ]);

            // Update order items
            foreach ($orderItemsData as $itemData) {
                if (isset($itemData['id'])) {
                    $orderItem = \App\Models\OrderItem::find($itemData['id']);
                    if ($orderItem) {
                        $orderItem->update([
                            'product_variant_id' => $itemData['product_variant_id'],
                            'quantity' => $itemData['quantity'],
                        ]);
                    }
                } else {
                    // ambil order_vendor_id dari product varian
                    $orderVendor = ProductVariant::find($itemData['product_variant_id'])->orderItems()->first()->orderVendor;

                    // Jika item tidak memiliki ID, berarti item baru, buat baru
                    \App\Models\OrderItem::create([
                        'order_vendor_id' => $orderVendor->id,
                        'product_variant_id' => $itemData['product_variant_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => ProductVariant::find($itemData['product_variant_id'])->price,
                        'total' => ProductVariant::find($itemData['product_variant_id'])->price * $itemData['quantity'],
                    ]);
                }
            }

            // Update shipment
            // $record->orderVendors()->with('shipment')->get()->map(function ($orderVendor) use ($data) {
            //     $shipment = $orderVendor->shipment;
            //     $shipment->update([
            //         'shipment_address_id' => $data['shipment_address_id'],
            //         'courier' => $data['courier'],
            //         'service' => $data['service'],
            //         'shipping_cost' => $data['shipping_cost'],
            //     ]);
            // })->first();

            // Update payment
            // $payment = $record->payment;

            // $payment->update([
            //     'payment_method' => $data['payment_method'],
            //     'status' => $record->payment_status,
            //     'amount' => $record->total_amount,

            // ]);





            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }


        return $record;
    }
}