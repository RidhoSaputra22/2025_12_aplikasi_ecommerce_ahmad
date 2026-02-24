<?php

namespace App\Filament\Pages;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use UnitEnum;

class OrderReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static null|UnitEnum|string $navigationGroup = 'Order';

    protected static ?string $navigationLabel = 'Laporan Order';

    protected static ?string $title = 'Laporan Order';

    protected static ?string $slug = 'order-report';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.order-report';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    #[Url]
    public ?string $status = null;

    #[Url]
    public ?string $paymentStatus = null;

    public function mount(): void
    {
        // Default: bulan ini
        $this->startDate = $this->startDate ?? now()->startOfMonth()->toDateString();
        $this->endDate = $this->endDate ?? now()->endOfDay()->toDateString();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->live(),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->native(false)
                            ->live(),
                        Select::make('status')
                            ->label('Status Order')
                            ->options(
                                collect(OrderStatus::cases())
                                    ->mapWithKeys(fn ($s) => [$s->value => $s->getLabel()])
                                    ->all()
                            )
                            ->placeholder('Semua Status')
                            ->live(),
                        Select::make('paymentStatus')
                            ->label('Status Pembayaran')
                            ->options(
                                collect(OrderPaymentStatus::cases())
                                    ->mapWithKeys(fn ($s) => [$s->value => $s->getLabel()])
                                    ->all()
                            )
                            ->placeholder('Semua Status')
                            ->live(),
                    ])
                    ->columns(4),
            ])
            ->statePath('');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->downloadPdf()),
        ];
    }

    public function getOrders()
    {
        return Order::query()
            ->with([
                'user',
                'payment',
                'orderVendors.vendor',
                'orderVendors.orderItems.productVariant.product',
            ])
            ->when($this->startDate, fn (Builder $q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn (Builder $q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when($this->status, fn (Builder $q) => $q->where('status', $this->status))
            ->when($this->paymentStatus, fn (Builder $q) => $q->where('payment_status', $this->paymentStatus))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function downloadPdf()
    {
        $orders = $this->getOrders();

        if ($orders->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Tidak ada order yang ditemukan untuk filter yang dipilih.')
                ->warning()
                ->send();

            return null;
        }

        $totalAmount = $orders->sum('total_amount');
        $totalPaid = $orders->where('payment_status', OrderPaymentStatus::Paid)->sum('total_amount');

        $pdf = Pdf::loadView('reports.orders-pdf', [
            'orders' => $orders,
            'startDate' => $this->startDate ? Carbon::parse($this->startDate)->translatedFormat('d F Y') : '-',
            'endDate' => $this->endDate ? Carbon::parse($this->endDate)->translatedFormat('d F Y') : '-',
            'status' => $this->status ? OrderStatus::from($this->status)->getLabel() : 'Semua',
            'paymentStatus' => $this->paymentStatus ? OrderPaymentStatus::from($this->paymentStatus)->getLabel() : 'Semua',
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])
            ->setPaper('a4', 'landscape');

        $filename = 'laporan-order-' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }
}
