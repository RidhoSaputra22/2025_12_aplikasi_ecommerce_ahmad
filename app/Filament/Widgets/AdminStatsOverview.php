<?php

namespace App\Filament\Widgets;

use App\Enums\OrderPaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserStatus;
use App\Enums\VendorStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Ringkasan Marketplace';

    protected ?string $description = 'Statistik utama order, produk, vendor, dan customer.';

    protected int | array | null $columns = 3;

    protected function getStats(): array
    {
        $paidRevenue = (float) Order::query()
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->sum('total_amount');

        [$currentRevenue, $previousRevenue] = $this->getPaidRevenueComparison();
        [$currentOrders, $previousOrders] = $this->getRecentOrderComparison();
        $newVendorsThisMonth = $this->countNewThisMonth(Vendor::query());
        $newProductsLast30Days = $this->countNewInLastDays(Product::query(), 30);
        $newCustomersThisMonth = $this->countNewThisMonth(
            User::query()->whereHas('role', fn (Builder $query) => $query->where('name', 'customer'))
        );

        return [
            Stat::make('Omzet Dibayar', $this->formatCurrency($paidRevenue))
                ->description($this->formatTrendDescription($currentRevenue, $previousRevenue, 'dibanding 30 hari sebelumnya'))
                ->descriptionIcon($this->getTrendIcon($currentRevenue, $previousRevenue))
                ->descriptionColor($this->getTrendColor($currentRevenue, $previousRevenue))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->chart($this->getDailyRevenueSparkline())
                ->url(OrderResource::getUrl()),

            Stat::make('Total Order', number_format(Order::query()->count(), 0, ',', '.'))
                ->description($this->formatTrendDescription($currentOrders, $previousOrders, 'dibanding 30 hari sebelumnya'))
                ->descriptionIcon($this->getTrendIcon($currentOrders, $previousOrders))
                ->descriptionColor($this->getTrendColor($currentOrders, $previousOrders))
                ->icon('heroicon-o-shopping-bag')
                ->color('primary')
                ->chart($this->getDailyOrderSparkline())
                ->url(OrderResource::getUrl()),

            Stat::make(
                'Vendor Aktif',
                number_format(
                    Vendor::query()
                        ->where('status', VendorStatus::Active->value)
                        ->count(),
                    0,
                    ',',
                    '.',
                ),
            )
                ->description("{$newVendorsThisMonth} vendor baru bulan ini")
                ->descriptionIcon('heroicon-m-building-storefront')
                ->descriptionColor('success')
                ->icon('heroicon-o-building-storefront')
                ->color('success')
                ->chart($this->getMonthlyCreatedSparkline(Vendor::query()))
                ->url(VendorResource::getUrl()),

            Stat::make(
                'Produk Aktif',
                number_format(
                    Product::query()
                        ->where('status', ProductStatus::Active->value)
                        ->count(),
                    0,
                    ',',
                    '.',
                ),
            )
                ->description("{$newProductsLast30Days} produk ditambahkan 30 hari terakhir")
                ->descriptionIcon('heroicon-m-cube')
                ->descriptionColor('warning')
                ->icon('heroicon-o-cube')
                ->color('warning')
                ->chart($this->getMonthlyCreatedSparkline(Product::query()))
                ->url(ProductResource::getUrl()),

            Stat::make(
                'Customer Aktif',
                number_format(
                    User::query()
                        ->where('status', UserStatus::Active->value)
                        ->whereHas('role', fn (Builder $query) => $query->where('name', 'customer'))
                        ->count(),
                    0,
                    ',',
                    '.',
                ),
            )
                ->description("{$newCustomersThisMonth} customer baru bulan ini")
                ->descriptionIcon('heroicon-m-user-plus')
                ->descriptionColor('info')
                ->icon('heroicon-o-users')
                ->color('info')
                ->chart(
                    $this->getMonthlyCreatedSparkline(
                        User::query()->whereHas('role', fn (Builder $query) => $query->where('name', 'customer'))
                    )
                )
                ->url(UserResource::getUrl()),
        ];
    }

    protected function getPaidRevenueComparison(): array
    {
        [$currentStart, $previousStart, $previousEnd] = $this->getComparisonRange();

        $current = (float) Order::query()
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->whereBetween('created_at', [$currentStart, now()])
            ->sum('total_amount');

        $previous = (float) Order::query()
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_amount');

        return [$current, $previous];
    }

    protected function getRecentOrderComparison(): array
    {
        [$currentStart, $previousStart, $previousEnd] = $this->getComparisonRange();

        $current = Order::query()
            ->whereBetween('created_at', [$currentStart, now()])
            ->count();

        $previous = Order::query()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        return [$current, $previous];
    }

    protected function getComparisonRange(): array
    {
        $currentStart = now()->startOfDay()->subDays(29);
        $previousStart = (clone $currentStart)->subDays(30);
        $previousEnd = (clone $currentStart)->subSecond();

        return [$currentStart, $previousStart, $previousEnd];
    }

    protected function getDailyRevenueSparkline(): array
    {
        $start = now()->startOfDay()->subDays(6);

        $revenueByDay = Order::query()
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total_amount'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'))
            ->map(fn ($orders) => (float) $orders->sum('total_amount'));

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $revenueByDay): float {
                $dateKey = (clone $start)->addDays($offset)->format('Y-m-d');

                return (float) ($revenueByDay[$dateKey] ?? 0);
            })
            ->all();
    }

    protected function getDailyOrderSparkline(): array
    {
        $start = now()->startOfDay()->subDays(6);

        $ordersByDay = Order::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'))
            ->map(fn ($orders) => (float) $orders->count());

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $ordersByDay): float {
                $dateKey = (clone $start)->addDays($offset)->format('Y-m-d');

                return (float) ($ordersByDay[$dateKey] ?? 0);
            })
            ->all();
    }

    protected function getMonthlyCreatedSparkline(Builder $query, int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $createdByMonth = (clone $query)
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($record) => Carbon::parse($record->created_at)->format('Y-m'))
            ->map(fn ($records) => (float) $records->count());

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($start, $createdByMonth): float {
                $monthKey = (clone $start)->addMonths($offset)->format('Y-m');

                return (float) ($createdByMonth[$monthKey] ?? 0);
            })
            ->all();
    }

    protected function countNewThisMonth(Builder $query): int
    {
        return (clone $query)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
    }

    protected function countNewInLastDays(Builder $query, int $days): int
    {
        return (clone $query)
            ->where('created_at', '>=', now()->startOfDay()->subDays($days - 1))
            ->count();
    }

    protected function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    protected function formatTrendDescription(float|int $current, float|int $previous, string $suffix): string
    {
        if ($this->isNearZero($current) && $this->isNearZero($previous)) {
            return 'Belum ada perubahan pada periode ini';
        }

        if ($this->isNearZero($previous)) {
            return 'Naik dari 0 pada periode sebelumnya';
        }

        $difference = (($current - $previous) / $previous) * 100;
        $direction = $difference >= 0 ? 'Naik' : 'Turun';

        return $direction . ' ' . number_format(abs($difference), 1, ',', '.') . '% ' . $suffix;
    }

    protected function getTrendIcon(float|int $current, float|int $previous): string
    {
        return $current >= $previous
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';
    }

    protected function getTrendColor(float|int $current, float|int $previous): string
    {
        return $current >= $previous ? 'success' : 'danger';
    }

    protected function isNearZero(float|int $value): bool
    {
        return abs((float) $value) < 0.000001;
    }
}
