<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

        if ($order) {
            return redirect()
                ->route('user.dashboard', [
                    'tab' => 'order-detail',
                    'order_id' => $order->id,
                ])
                ->with('success', $this->getFinishMessage($transactionStatus));
        }

        return redirect()->route('user.dashboard', ['tab' => 'history'])
            ->with('success', 'Pembayaran sedang diproses.');
    }

    /**
     * Pesan berdasarkan status transaksi.
     */
    private function getFinishMessage(string $status): string
    {
        return match ($status) {
            'settlement', 'capture' => 'Pembayaran berhasil! Pesanan Anda sedang diproses.',
            'pending' => 'Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran sesuai instruksi.',
            'deny', 'cancel', 'expire' => 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.',
            default => 'Status pembayaran sedang diproses.',
        };
    }
}
