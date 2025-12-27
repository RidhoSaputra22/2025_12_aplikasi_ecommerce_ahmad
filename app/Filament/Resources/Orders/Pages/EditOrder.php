<?php

namespace App\Filament\Resources\Orders\Pages;

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

    protected function fillForm(): void
    {
        parent::fillForm();

        $order = $this->record;
        $orderVendors = $order->orderVendors();
        $orderItems = [];

        $orderItems = $orderVendors->with('orderItems')->get()->flatMap(function ($orderVendor) {
            return $orderVendor->orderItems->toArray();
        })->toArray();


        $shipment = $order->orderVendors()->with('shipment')->first()->shipment;
        $payment = $order->payment;

        // dd($shipment->shipment_address_id);

        $this->form->fill([
            'orderItems' => $orderItems,
            'user_id' => $order->user_id,
            'shipment_address_id' => $shipment->shipment_address_id,
            'courier' => $shipment->courier,
            'service' => $shipment->service,
            'shipping_cost' => $shipment->shipping_cost,
            'payment_method' => $payment->payment_method,
            'payment_status' => $order->payment_status,
            'status' => $order->status,



        ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {

            $orderItemsData = $data['orderItems'];


            // Update Order Items
            $record->orderItems()->delete();
            foreach ($orderItemsData as $itemData) {
                $record->orderItems()->create($itemData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }


        return $record;
    }
}
