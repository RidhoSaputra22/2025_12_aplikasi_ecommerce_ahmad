<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidPaymentSignatureException;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller untuk menangani webhook/notification dari Midtrans.
 *
 * Endpoint ini dipanggil oleh server Midtrans (server-to-server)
 * saat terjadi perubahan status transaksi. Endpoint harus:
 * - Bersifat public (tanpa auth middleware)
 * - Mengecualikan CSRF verification
 * - Mengembalikan 2xx hanya setelah notification berhasil diproses
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
     * Error sementara mengembalikan 500 agar gateway dapat melakukan retry.
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $validator = Validator::make($payload, [
                'order_id' => ['required', 'string', 'max:255'],
                'status_code' => ['required', 'string', 'max:10'],
                'gross_amount' => ['required', 'numeric', 'min:0'],
                'signature_key' => ['required', 'string', 'size:128'],
                'transaction_status' => ['required', 'string', 'max:50'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid notification payload.',
                ], 422);
            }

            Log::channel('payment')->info('Midtrans webhook received', [
                'order_id' => $payload['order_id'] ?? 'unknown',
                'transaction_status' => $payload['transaction_status'] ?? 'unknown',
                'payment_type' => $payload['payment_type'] ?? 'unknown',
            ]);

            $this->paymentService->processNotification($payload);

            return response()->json(['status' => 'ok']);

        } catch (InvalidPaymentSignatureException $e) {
            Log::channel('payment')->warning('Midtrans webhook signature rejected', [
                'order_id' => $request->input('order_id', 'unknown'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature.',
            ], 403);
        } catch (\Throwable $e) {
            Log::channel('payment')->error('Midtrans webhook error', [
                'error' => $e->getMessage(),
                'order_id' => $request->input('order_id', 'unknown'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Notification could not be processed.',
            ], 500);
        }
    }
}
