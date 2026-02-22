<?php

namespace App\Contracts;

use App\DataTransfers\Payment\MidtransCallbackData;
use App\DataTransfers\Payment\SnapTransactionData;

/**
 * Interface untuk payment gateway.
 *
 * Abstraksi ini memungkinkan penggantian payment gateway
 * tanpa mengubah business logic di PaymentService.
 */
interface PaymentGatewayInterface
{
    /**
     * Buat transaksi baru dan dapatkan snap token / redirect URL.
     *
     * @return array{token: string, redirect_url: string}
     *
     * @throws \Exception
     */
    public function createTransaction(SnapTransactionData $data): array;

    /**
     * Verifikasi dan proses notification dari payment gateway.
     *
     * @param  array  $payload  Raw notification body
     *
     * @throws \Exception
     */
    public function handleNotification(array $payload): MidtransCallbackData;

    /**
     * Cek status transaksi di payment gateway.
     *
     * @throws \Exception
     */
    public function getTransactionStatus(string $orderId): MidtransCallbackData;

    /**
     * Batalkan transaksi.
     *
     * @throws \Exception
     */
    public function cancelTransaction(string $orderId): bool;
}
