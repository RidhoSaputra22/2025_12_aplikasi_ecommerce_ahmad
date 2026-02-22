<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk menangani webhook/notification dari Midtrans.
 *
 * Endpoint ini dipanggil oleh server Midtrans (server-to-server)
 * saat terjadi perubahan status transaksi. Endpoint harus:
 * - Bersifat public (tanpa auth middleware)
 * - Mengecualikan CSRF verification
 * - Selalu mengembalikan HTTP 200
 */
class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Handle notification dari Midtrans.
     *
     * URL: POST /api/midtrans/notification
     *
     * Response selalu 200 OK agar Midtrans tidak retry.
     * Jika terjadi error, kita log dan tetap return 200.
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            Log::channel('payment')->info('Midtrans webhook received', [
                'order_id' => $payload['order_id'] ?? 'unknown',
                'transaction_status' => $payload['transaction_status'] ?? 'unknown',
                'payment_type' => $payload['payment_type'] ?? 'unknown',
            ]);

            $this->paymentService->processNotification($payload);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Midtrans webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Tetap return 200 agar Midtrans tidak retry terus-menerus
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
