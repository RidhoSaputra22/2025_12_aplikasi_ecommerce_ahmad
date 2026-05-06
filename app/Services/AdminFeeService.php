<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\PlatformSetting;

class AdminFeeService
{
    protected ?float $cachedPercentage = null;

    public function getCurrentPercentage(): float
    {
        if ($this->cachedPercentage !== null) {
            return $this->cachedPercentage;
        }

        return $this->cachedPercentage = $this->normalizePercentage(
            (float) PlatformSetting::current()->admin_fee_percentage
        );
    }

    /**
     * @return array{
     *     gross_amount: float,
     *     admin_fee_percentage: float,
     *     admin_fee_amount: float,
     *     vendor_payout_amount: float
     * }
     */
    public function calculateBreakdown(float $grossAmount, ?float $percentage = null): array
    {
        $grossAmount = max(round($grossAmount, 2), 0);
        $percentage = $this->normalizePercentage($percentage ?? $this->getCurrentPercentage());
        $adminFeeAmount = round(($grossAmount * $percentage) / 100, 2);
        $vendorPayoutAmount = round(max($grossAmount - $adminFeeAmount, 0), 2);

        return [
            'gross_amount' => $grossAmount,
            'admin_fee_percentage' => $percentage,
            'admin_fee_amount' => $adminFeeAmount,
            'vendor_payout_amount' => $vendorPayoutAmount,
        ];
    }

    /**
     * Gunakan snapshot yang sudah tersimpan bila ada.
     *
     * @return array{
     *     gross_amount: float,
     *     admin_fee_percentage: float,
     *     admin_fee_amount: float,
     *     vendor_payout_amount: float
     * }
     */
    public function resolveBreakdown(OrderVendor $orderVendor): array
    {
        if (
            $orderVendor->admin_fee_percentage !== null
            && $orderVendor->admin_fee_amount !== null
            && $orderVendor->vendor_payout_amount !== null
        ) {
            return [
                'gross_amount' => round((float) $orderVendor->subtotal, 2),
                'admin_fee_percentage' => round((float) $orderVendor->admin_fee_percentage, 2),
                'admin_fee_amount' => round((float) $orderVendor->admin_fee_amount, 2),
                'vendor_payout_amount' => round((float) $orderVendor->vendor_payout_amount, 2),
            ];
        }

        return $this->calculateBreakdown((float) $orderVendor->subtotal);
    }

    public function syncOrderVendor(OrderVendor $orderVendor, ?float $percentage = null): OrderVendor
    {
        $breakdown = $this->calculateBreakdown((float) $orderVendor->subtotal, $percentage);

        $orderVendor->update([
            'admin_fee_percentage' => $breakdown['admin_fee_percentage'],
            'admin_fee_amount' => $breakdown['admin_fee_amount'],
            'vendor_payout_amount' => $breakdown['vendor_payout_amount'],
        ]);

        return $orderVendor->refresh();
    }

    public function getOrderAdminFeeTotal(Order $order): float
    {
        return round(
            $order->orderVendors->sum(fn (OrderVendor $orderVendor) => $this->resolveBreakdown($orderVendor)['admin_fee_amount']),
            2
        );
    }

    public function getOrderVendorPayoutTotal(Order $order): float
    {
        return round(
            $order->orderVendors->sum(fn (OrderVendor $orderVendor) => $this->resolveBreakdown($orderVendor)['vendor_payout_amount']),
            2
        );
    }

    protected function normalizePercentage(float $percentage): float
    {
        return round(min(max($percentage, 0), 100), 2);
    }
}
