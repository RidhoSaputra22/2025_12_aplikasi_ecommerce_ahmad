<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class ShipmentTrackingService
{
    protected float $originLat;

    protected float $originLng;

    protected float $destLat;

    protected float $destLng;

    protected int $travelDurationSeconds;

    protected float $speedMultiplier;

    public function __construct()
    {
        $this->originLat = (float) config('shipping.origin.lat');
        $this->originLng = (float) config('shipping.origin.lng');
        $this->destLat = (float) config('shipping.destination.lat');
        $this->destLng = (float) config('shipping.destination.lng');
        $this->travelDurationSeconds = max(
            (int) (config('shipping.travel_duration_hours', 6) * 3600),
            1,
        );
        $this->speedMultiplier = max((float) config('shipping.speed_multiplier', 1), 0.000001);
    }

    /**
     * Calculate current ship position based on shipped_at timestamp.
     *
     * @return array{lat: float, lng: float, progress: float, heading: float, eta: string|null, shipped_at: string, is_arrived: bool, distance_remaining_km: float}
     */
    public function calculateShipPosition(Shipment $shipment): array
    {
        if (! $shipment->shipped_at) {
            return $this->buildResponse(
                $this->originLat,
                $this->originLng,
                0.0,
                $this->calculateBearing($this->originLat, $this->originLng, $this->destLat, $this->destLng),
                null,
                null,
                false
            );
        }

        $shippedAt = Carbon::parse($shipment->shipped_at);

        // Seconds since the ship departed (negative if shipped_at is in the future)
        $elapsed = now()->getTimestamp() - $shippedAt->getTimestamp();

        // If shipped_at is in the future, ship hasn't departed yet
        if ($elapsed < 0) {
            $elapsed = 0;
        }

        // Apply speed multiplier — higher value = faster ship (for testing)
        $elapsed = $elapsed * $this->speedMultiplier;

        $progress = min(max($elapsed / $this->travelDurationSeconds, 0), 1.0);

        // Linear interpolation
        $currentLat = $this->originLat + ($this->destLat - $this->originLat) * $progress;
        $currentLng = $this->originLng + ($this->destLng - $this->originLng) * $progress;

        // Calculate heading (bearing from current position to destination)
        $heading = $this->calculateBearing($currentLat, $currentLng, $this->destLat, $this->destLng);

        // ETA calculation
        $eta = null;
        if ($progress < 1.0) {
            $remainingSeconds = ($this->travelDurationSeconds - $elapsed) / $this->speedMultiplier;
            $eta = now()->addSeconds((int) $remainingSeconds)->toIso8601String();
        }

        $isArrived = $progress >= 1.0;

        return $this->buildResponse(
            $currentLat,
            $currentLng,
            $progress,
            $heading,
            $eta,
            $shippedAt->toIso8601String(),
            $isArrived
        );
    }

    /**
     * Calculate bearing between two coordinates in degrees.
     */
    protected function calculateBearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $dLng = deg2rad($lng2 - $lng1);

        $y = sin($dLng) * cos($lat2Rad);
        $x = cos($lat1Rad) * sin($lat2Rad) - sin($lat1Rad) * cos($lat2Rad) * cos($dLng);

        $bearing = rad2deg(atan2($y, $x));

        return fmod($bearing + 360, 360);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula (in km).
     */
    public function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get the total route distance in km.
     */
    public function getTotalDistanceKm(): float
    {
        return $this->calculateDistanceKm(
            $this->originLat,
            $this->originLng,
            $this->destLat,
            $this->destLng
        );
    }

    /**
     * Get origin coordinates.
     */
    public function getOrigin(): array
    {
        return [
            'lat' => $this->originLat,
            'lng' => $this->originLng,
            'name' => config('shipping.origin.name'),
        ];
    }

    /**
     * Get destination coordinates.
     */
    public function getDestination(): array
    {
        return [
            'lat' => $this->destLat,
            'lng' => $this->destLng,
            'name' => config('shipping.destination.name'),
        ];
    }

    /**
     * Build standardized response array.
     */
    protected function buildResponse(
        float $lat,
        float $lng,
        float $progress,
        float $heading,
        ?string $eta,
        ?string $shippedAt,
        bool $isArrived
    ): array {
        $totalDistanceKm = $this->getTotalDistanceKm();
        $distanceRemainingKm = $totalDistanceKm * (1 - $progress);

        return [
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
            'progress' => round($progress, 4),
            'heading' => round($heading, 2),
            'eta' => $eta,
            'shipped_at' => $shippedAt,
            'is_arrived' => $isArrived,
            'distance_remaining_km' => round($distanceRemainingKm, 2),
            'total_distance_km' => round($totalDistanceKm, 2),
        ];
    }
}
