<?php

namespace App\Livewire\User\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Livewire component untuk halaman pembayaran Midtrans.
 *
 * Halaman ini menampilkan:
 * - Ringkasan pesanan
 * - Pilihan metode pembayaran (manual / Midtrans)
 * - Snap popup Midtrans
 * - Status pembayaran real-time
 */
#[Layout('layouts.app')]
class PaymentPage extends Component
{
    public ?int $orderId = null;

    public string $paymentMethod = 'midtrans'; // Default ke midtrans

    public ?string $snapToken = null;

    public ?string $snapRedirectUrl = null;

    public bool $isProcessing = false;

    public ?string $errorMessage = null;

    public function mount(int $orderId): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        $this->orderId = $orderId;

        $order = $this->getOrder();

        if (! $order) {
            session()->flash('error', 'Pesanan tidak ditemukan.');
            $this->redirectRoute('user.dashboard', ['tab' => 'history']);

            return;
        }

        // Jika sudah ada snap token, langsung gunakan
        if ($order->payment?->snap_token) {
            $this->snapToken = $order->payment->snap_token;
            $this->snapRedirectUrl = $order->payment->snap_redirect_url;
        }
    }

    public function getOrder(): ?Order
    {
        if (! $this->orderId || ! Auth::check()) {
            return null;
        }

        return Order::with([
            'payment',
            'orderVendors.vendor',
            'orderVendors.orderItems.productVariant.product.productImages',
            'orderVendors.shipment.shipmentCourier',
            'orderVendors.shipment.shipmentAddress',
        ])
            ->where('id', $this->orderId)
            ->where('user_id', Auth::id())
            ->first();
    }

    /**
     * Proses pembayaran via Midtrans Snap.
     */
    public function payWithMidtrans(): void
    {
        $this->errorMessage = null;
        $this->isProcessing = true;

        try {
            $order = $this->getOrder();

            if (! $order) {
                $this->errorMessage = 'Pesanan tidak ditemukan.';
                $this->isProcessing = false;

                return;
            }

            $payment = $order->payment;

            if (! $payment) {
                $this->errorMessage = 'Data pembayaran tidak ditemukan.';
                $this->isProcessing = false;

                return;
            }

            if ($order->status === OrderStatus::Cancelled) {
                $this->errorMessage = 'Pesanan sudah dibatalkan dan tidak dapat dibayar kembali.';
                $this->isProcessing = false;

                return;
            }

            // Hanya boleh bayar jika status pending atau failed
            if (! in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::Failed])) {
                $this->errorMessage = 'Status pembayaran tidak valid untuk melakukan pembayaran.';
                $this->isProcessing = false;

                return;
            }

            // Jika sudah ada snap token yang masih valid, gunakan kembali
            if ($payment->snap_token && $payment->expired_at && $payment->expired_at->isFuture()) {
                $this->snapToken = $payment->snap_token;
                $this->snapRedirectUrl = $payment->snap_redirect_url;
                $this->isProcessing = false;
                $this->dispatch('open-snap', token: $this->snapToken);

                return;
            }

            /** @var PaymentService $paymentService */
            $paymentService = app(PaymentService::class);
            $result = $paymentService->createMidtransPayment($order);

            $this->snapToken = $result['token'];
            $this->snapRedirectUrl = $result['redirect_url'];
            $this->isProcessing = false;

            // Dispatch event ke frontend untuk membuka Snap popup
            $this->dispatch('open-snap', token: $this->snapToken);

        } catch (\Exception $e) {
            Log::channel('payment')->error('PaymentPage: Failed to create payment', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);

            $this->errorMessage = 'Gagal memproses pembayaran. Silakan coba lagi.';
            $this->isProcessing = false;
        }
    }

    /**
     * Sync status pembayaran dari Midtrans.
     * Dipanggil setelah redirect dari Snap atau secara manual.
     */
    public function syncPaymentStatus(): void
    {
        try {
            $order = $this->getOrder();

            if (! $order || ! $order->payment || ! $order->payment->snap_token) {
                return;
            }

            if ($order->payment->status === PaymentStatus::Success) {
                return;
            }

            /** @var PaymentService $paymentService */
            $paymentService = app(PaymentService::class);
            $paymentService->syncPaymentStatus($order);

        } catch (\Exception $e) {
            Log::channel('payment')->warning('Failed to sync payment status from page', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('user.payment.payment-page', [
            'order' => $this->getOrder(),
        ]);
    }
}
