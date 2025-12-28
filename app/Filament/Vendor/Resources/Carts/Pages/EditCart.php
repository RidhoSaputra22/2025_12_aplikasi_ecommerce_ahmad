<?php

namespace App\Filament\Vendor\Resources\Carts\Pages;

use App\Models\ProductVariant;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Vendor\Resources\Carts\CartResource;

class EditCart extends EditRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $data['items'] = $this->record->cartItems->map(function ($item) {
            return [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        return parent::mutateFormDataBeforeFill($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        $cart = $record;
        $cart->user_id = $data['user_id'];
        $cart->save();

        // Sync cart items
        $cart->cartItems()->delete();
        foreach ($data['items'] as $item) {
            $productVariant = ProductVariant::find($item['product_variant_id']);
            $item['price'] = $productVariant->price;
            $cart->cartItems()->create([
                'cart_id' => $cart->id,
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        return parent::handleRecordUpdate($record, $data);
    }
}
