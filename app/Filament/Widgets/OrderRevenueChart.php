<?php

namespace App\Filament\Widgets;

use App\Enums\OrderPaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderRevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '120s';

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Tren Omzet dan Order';

    protected ?string $description = 'Perbandingan omzet dibayar dan jumlah order berdasarkan periode.';

    protected ?string $maxHeight = '520px';

    public ?string $filter = '30_days';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7_days' => '7 Hari',
            '30_days' => '30 Hari',
            '12_months' => '12 Bulan',
        ];
    }

    protected function getData(): array
    {
        return match ($this->filter) {
            '7_days' => $this->getDailyChartData(7),
            '12_months' => $this->getMonthlyChartData(12),
            default => $this->getDailyChartData(30),
        };
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.35,
                ],
            ],
            'scales' => [
                'money' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                ],
                'orders' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getDailyChartData(int $days): array
    {
        $start = now()->startOfDay()->subDays($days - 1);

        $orders = Order::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total_amount', 'payment_status']);

        $groupedOrders = $orders->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'));

        $labels = [];
        $revenues = [];
        $counts = [];

        foreach (range(0, $days - 1) as $offset) {
            $date = (clone $start)->addDays($offset);
            $key = $date->format('Y-m-d');
            $entries = $groupedOrders->get($key, collect());

            $labels[] = $date->translatedFormat('d M');
            $counts[] = $entries->count();
            $revenues[] = (float) $entries
                ->filter(fn (Order $order) => $order->payment_status === OrderPaymentStatus::Paid)
                ->sum('total_amount');
        }

        return $this->makeChartPayload($labels, $revenues, $counts);
    }

    protected function getMonthlyChartData(int $months): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $orders = Order::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total_amount', 'payment_status']);

        $groupedOrders = $orders->groupBy(fn (Order $order) => $order->created_at->format('Y-m'));

        $labels = [];
        $revenues = [];
        $counts = [];

        foreach (range(0, $months - 1) as $offset) {
            $month = (clone $start)->addMonths($offset);
            $key = $month->format('Y-m');
            $entries = $groupedOrders->get($key, collect());

            $labels[] = $month->translatedFormat('M Y');
            $counts[] = $entries->count();
            $revenues[] = (float) $entries
                ->filter(fn (Order $order) => $order->payment_status === OrderPaymentStatus::Paid)
                ->sum('total_amount');
        }

        return $this->makeChartPayload($labels, $revenues, $counts);
    }

    protected function makeChartPayload(array $labels, array $revenues, array $counts): array
    {
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Omzet Dibayar',
                    'data' => $revenues,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'yAxisID' => 'money',
                    'fill' => true,
                ],
                [
                    'label' => 'Jumlah Order',
                    'data' => $counts,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'yAxisID' => 'orders',
                    'fill' => false,
                ],
            ],
        ];
    }
}
