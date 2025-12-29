<?php

namespace Database\Factories;

use App\Enums\ShipmentStatus;
use App\Models\OrderVendor;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(ShipmentStatus::cases());

        return [
            'order_vendor_id' => OrderVendor::factory(),
            'shipment_address_id' => ShipmentAddress::factory(),
            'shipment_courier_id' => fake()->boolean(80) ? ShipmentCourier::factory() : null,
            'tracking_number' => $status === ShipmentStatus::Pending ? null : fake()->unique()->bothify('TRK-##########'),
            'shipping_cost' => fake()->randomFloat(2, 5000, 200000),
            'status' => $status,
            'shipped_at' => $status !== ShipmentStatus::Pending ? fake()->dateTimeBetween('-14 days', 'now') : null,
            'delivered_at' => $status === ShipmentStatus::Delivered ? fake()->dateTimeBetween('-7 days', 'now') : null,
        ];
    }
}
