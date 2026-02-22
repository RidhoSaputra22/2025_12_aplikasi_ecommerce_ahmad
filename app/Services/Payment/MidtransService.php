<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransfers\Payment\MidtransCallbackData;
use App\DataTransfers\Payment\SnapTransactionData;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction;

/**
 * Service untuk interaksi langsung dengan Midtrans API.
 *
 * Class ini bertanggung jawab untuk:
 * - Konfigurasi Midtrans SDK
 * - Membuat Snap Token
 * - Memproses notification webhook
 * - Mengecek status transaksi
 * - Membatalkan transaksi
 *
 * Mengimplementasikan PaymentGatewayInterface untuk loose coupling.
 */
class MidtransService implements PaymentGatewayInterface
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    /**
     * Konfigurasi Midtrans SDK dari config/midtrans.php.
     */
    private function configureMidtrans(): void
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$clientKey = config('midtrans.client_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * {@inheritdoc}
     */
    public function createTransaction(SnapTransactionData $data): array
    {
        try {
            $params = $data->toMidtransParams();

            Log::channel('payment')->info('Midtrans: Creating snap transaction', [
                'order_id' => $data->orderNumber,
                'gross_amount' => $data->grossAmount,
            ]);

            $snapToken = Snap::getSnapToken($params);
            $snapUrl = $this->getSnapRedirectUrl($snapToken);

            Log::channel('payment')->info('Midtrans: Snap token created', [
                'order_id' => $data->orderNumber,
                'snap_token' => substr($snapToken, 0, 20) . '...',
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
            ];

        } catch (\Exception $e) {
            Log::channel('payment')->error('Midtrans: Failed to create snap token', [
                'order_id' => $data->orderNumber,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleNotification(array $payload): MidtransCallbackData
    {
        try {
            Log::channel('payment')->info('Midtrans: Processing notification', [
                'order_id' => $payload['order_id'] ?? 'unknown',
                'transaction_status' => $payload['transaction_status'] ?? 'unknown',
            ]);

            // Verifikasi signature key
            $this->verifySignature($payload);

            return MidtransCallbackData::fromNotification($payload);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Midtrans: Notification processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionStatus(string $orderId): MidtransCallbackData
    {
        try {
            $response = Transaction::status($orderId);
            $data = json_decode(json_encode($response), true);

            return MidtransCallbackData::fromNotification($data);

        } catch (\Exception $e) {
            Log::channel('payment')->error('Midtrans: Failed to get transaction status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancelTransaction(string $orderId): bool
    {
        try {
            Transaction::cancel($orderId);

            Log::channel('payment')->info('Midtrans: Transaction cancelled', [
                'order_id' => $orderId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::channel('payment')->error('Midtrans: Failed to cancel transaction', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Verifikasi signature key dari notification Midtrans.
     *
     * @throws \RuntimeException
     */
    private function verifySignature(array $payload): void
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            throw new \RuntimeException('Invalid Midtrans signature key.');
        }
    }

    /**
     * Dapatkan Snap redirect URL berdasarkan environment.
     */
    private function getSnapRedirectUrl(string $token): string
    {
        $baseUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        return $baseUrl . $token;
    }
}
