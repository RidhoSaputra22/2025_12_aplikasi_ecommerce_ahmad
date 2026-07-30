<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk redirect URL dari Midtrans Snap.
 *
 * Setelah customer selesai berinteraksi dengan halaman pembayaran Midtrans,
 * mereka akan di-redirect ke URL ini berdasarkan status pembayaran.
 */
class PaymentRedirectController extends Controller
{
    /**
     * Handle finish redirect dari Midtrans.
     *
     * Dipanggil setelah customer selesai melakukan pembayaran.
     * Status sebenarnya datang dari webhook notification.
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $transactionStatus = $request->get('transaction_status', 'unknown');

        Log::channel('payment')->info('Payment finish redirect', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
        ]);

        // Cari order
        $order = null;
        if ($orderId && Auth::check()) {
            $order = Order::query()
                ->where('order_number', $orderId)
                ->where('user_id', Auth::id())
                ->first();
        }

        // Sync payment status dari Midtrans agar status terupdate real-time
        if ($order && $order->payment && $order->payment->snap_token) {
            try {
                $paymentService = app(PaymentService::class);
                $paymentService->syncPaymentStatus($order);
                $order->refresh();
            } catch (\Exception $e) {
                Log::channel('payment')->warning('Failed to sync on redirect', [
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($order) {
            return redirect()
                ->route('user.dashboard', [
                    'tab' => 'order-detail',
                    'order_id' => $order->id,
                ])
                ->with('success', $this->getFinishMessage($order));
        }

        return redirect()->route('user.dashboard', ['tab' => 'history'])
            ->with('success', 'Pembayaran sedang diproses.');
    }

    /**
     * Pesan berdasarkan status transaksi.
     */
    private function getFinishMessage(Order $order): string
    {
        return match ($order->payment?->status) {
            PaymentStatus::Success => 'Pembayaran berhasil! Pesanan Anda sedang diproses.',
            PaymentStatus::Failed => 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.',
            default => 'Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran sesuai instruksi.',
        };
    }
}
