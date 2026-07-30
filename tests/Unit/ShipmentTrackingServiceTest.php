<?php

namespace Tests\Unit;

use App\Models\Shipment;
use App\Services\ShipmentTrackingService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShipmentTrackingServiceTest extends TestCase
{
    public function test_eta_accounts_for_the_speed_multiplier(): void
    {
        config()->set('shipping.travel_duration_hours', 6);
        config()->set('shipping.speed_multiplier', 10);
        Carbon::setTestNow('2026-07-30 10:00:00');

        $shipment = new Shipment;
        $shipment->shipped_at = now();

        $position = (new ShipmentTrackingService)->calculateShipPosition($shipment);
        $eta = Carbon::parse($position['eta']);

        $this->assertEqualsWithDelta(2160, now()->diffInSeconds($eta), 2);

        Carbon::setTestNow();
    }
}
