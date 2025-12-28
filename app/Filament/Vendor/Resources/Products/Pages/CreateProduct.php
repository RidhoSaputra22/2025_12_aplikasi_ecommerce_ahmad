<?php

namespace App\Filament\Vendor\Resources\Products\Pages;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Vendor\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $product = Product::create([
            'vendor_id' => Auth::user()->vendor->id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],

            'description' => $data['description'],
            'price' => $data['price'],
            'weight' => $data['weight'],
            'status' => $data['status']
        ]);

        return $product;
    }
}
