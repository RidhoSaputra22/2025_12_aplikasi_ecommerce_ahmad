<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\ProductVariant;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Services\AdminFeeService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->status === OrderStatus::Pending
                    && $this->record->payment_status === OrderPaymentStatus::Pending
                    && $this->record->orderVendors()->count() === 1)
                ->using(function (Model $record): bool {
                    return DB::transaction(function () use ($record): bool {
                        $lockedOrder = $record->newQuery()
                            ->whereKey($record->getKey())
                            ->where('status', OrderStatus::Pending)
                            ->where('payment_status', OrderPaymentStatus::Pending)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedOrder) {
                            return false;
                        }

                        $items = $lockedOrder->orderVendors()
                            ->with('orderItems')
                            ->get()
                            ->flatMap->orderItems;

                        foreach ($items as $item) {
                            ProductVariant::query()
                                ->whereKey($item->product_variant_id)
                                ->increment('stock', (int) $item->quantity);
                        }

                        return (bool) $lockedOrder->delete();
                    });
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $order = $this->record;
        $orderVendor = $order->orderVendors()->with(['orderItems', 'shipment'])->first();
        $orderItems = $orderVendor?->orderItems->toArray() ?? [];
        $shipment = $orderVendor?->shipment;

        $payment = $order->payment;

        $data = array_merge($data, [
            'items' => $orderItems,
            'user_id' => $order->user_id,
            'vendor_id' => $orderVendor?->vendor_id,
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
            $adminFeeService = app(AdminFeeService::class);

            $record = $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            if (
                $record->status !== OrderStatus::Pending
                || $record->payment_status !== OrderPaymentStatus::Pending
                || $record->orderVendors()->count() !== 1
            ) {
                throw ValidationException::withMessages([
                    'items' => 'Hanya order pending dengan satu vendor yang dapat diedit.',
                ]);
            }

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

            // Update order main data
            $record->update([
                'user_id' => $data['user_id'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
            ]);

            // Get or create order vendor
            $orderVendor = $record->orderVendors()->lockForUpdate()->firstOrFail();
            $oldItems = $orderVendor->orderItems()->lockForUpdate()->get();

            // Kembalikan reservasi stok lama sebelum menghitung susunan item baru.
            foreach ($oldItems as $oldItem) {
                ProductVariant::query()
                    ->whereKey($oldItem->product_variant_id)
                    ->increment('stock', (int) $oldItem->quantity);
            }

            // Delete existing order items
            $orderVendor->orderItems()->delete();

            // Calculate subtotal and create new order items
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

                $variant->decrement('stock', $quantity);
            }

            // Update order vendor subtotal
            $orderVendor->update(['subtotal' => $subtotal, 'vendor_id' => $data['vendor_id']]);
            $adminFeeService->syncOrderVendor($orderVendor);

            // Update or create shipment
            $shipment = $orderVendor->shipment;
            $shippingCost = (float) $courier->price;

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
                    'status' => ShipmentStatus::Pending,
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
