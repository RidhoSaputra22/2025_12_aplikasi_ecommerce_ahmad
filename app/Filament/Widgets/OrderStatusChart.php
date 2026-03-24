<?php

namespace App\Filament\Widgets;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '120s';

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Distribusi Status';

    protected ?string $description = 'Sebaran status order utama dan status pembayaran di sistem.';

    protected ?string $maxHeight = '320px';

    public ?string $filter = 'order_status';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getFilters(): ?array
    {
        return [
            'order_status' => 'Status Order',
            'payment_status' => 'Status Pembayaran',
        ];
    }

    protected function getData(): array
    {
        return $this->filter === 'payment_status'
            ? $this->getPaymentStatusData()
            : $this->getOrderStatusData();
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '62%',
        ];
    }

    protected function getOrderStatusData(): array
    {
        $totals = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];

        foreach (OrderStatus::cases() as $status) {
            $labels[] = $status->getLabel();
            $data[] = (int) ($totals[$status->value] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Order',
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b',
                        '#3b82f6',
                        '#6366f1',
                        '#16a34a',
                        '#ef4444',
                    ],
                ],
            ],
        ];
    }

    protected function getPaymentStatusData(): array
    {
        $totals = Order::query()
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $labels = [];
        $data = [];

        foreach (OrderPaymentStatus::cases() as $status) {
            $labels[] = $status->getLabel();
            $data[] = (int) ($totals[$status->value] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Status Pembayaran',
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b',
                        '#60a5fa',
                        '#16a34a',
                        '#ef4444',
                    ],
                ],
            ],
        ];
    }
}
