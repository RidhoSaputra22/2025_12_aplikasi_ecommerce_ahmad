<?php

use App\Models\Shipment;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('shipment.{shipmentId}', function ($user, int $shipmentId): bool {
    if ($user->role?->name === 'admin') {
        return true;
    }

    return Shipment::query()
        ->whereKey($shipmentId)
        ->where(function ($query) use ($user) {
            $query
                ->whereHas('orderVendor.order', fn ($orderQuery) => $orderQuery->where('user_id', $user->id))
                ->orWhereHas('orderVendor.vendor', fn ($vendorQuery) => $vendorQuery->where('user_id', $user->id));
        })
        ->exists();
});
