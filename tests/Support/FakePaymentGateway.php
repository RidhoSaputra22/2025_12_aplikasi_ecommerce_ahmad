<?php

namespace Tests\Support;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransfers\Payment\MidtransCallbackData;
use App\DataTransfers\Payment\SnapTransactionData;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public function createTransaction(SnapTransactionData $data): array
    {
        return [
            'token' => 'test-snap-token',
            'redirect_url' => 'https://payment.example.test/snap',
        ];
    }

    public function handleNotification(array $payload): MidtransCallbackData
    {
        return MidtransCallbackData::fromNotification($payload);
    }

    public function getTransactionStatus(string $orderId): MidtransCallbackData
    {
        return MidtransCallbackData::fromNotification([
            'order_id' => $orderId,
            'transaction_id' => 'test-transaction',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'gross_amount' => 120_000,
        ]);
    }

    public function cancelTransaction(string $orderId): bool
    {
        return true;
    }
}
