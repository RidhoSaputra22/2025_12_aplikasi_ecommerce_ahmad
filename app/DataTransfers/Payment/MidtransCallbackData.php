<?php

namespace App\DataTransfers\Payment;

/**
 * DTO untuk hasil callback/notification dari Midtrans.
 *
 * Normalisasi data dari berbagai format response Midtrans
 * ke struktur data yang konsisten untuk diproses oleh PaymentService.
 */
final readonly class MidtransCallbackData
{
    public function __construct(
        public string  $orderId,
        public string  $transactionId,
        public string  $transactionStatus,
        public string  $paymentType,
        public int     $grossAmount,
        public ?string $fraudStatus = null,
        public ?string $vaNumber = null,
        public ?string $bank = null,
        public ?string $store = null,
        public ?string $paymentCode = null,
        public ?string $qrUrl = null,
        public ?string $deeplinkUrl = null,
        public ?string $signatureKey = null,
        public ?string $statusCode = null,
        public ?string $statusMessage = null,
        public array   $rawResponse = [],
    ) {}

    /**
     * Buat instance dari notification body Midtrans.
     */
    public static function fromNotification(array $data): self
    {
        // Ekstrak VA number dari berbagai format
        $vaNumber = null;
        $bank = null;

        if (!empty($data['va_numbers'])) {
            $vaNumber = $data['va_numbers'][0]['va_number'] ?? null;
            $bank = $data['va_numbers'][0]['bank'] ?? null;
        } elseif (!empty($data['permata_va_number'])) {
            $vaNumber = $data['permata_va_number'];
            $bank = 'permata';
        }

        return new self(
            orderId: $data['order_id'] ?? '',
            transactionId: $data['transaction_id'] ?? '',
            transactionStatus: $data['transaction_status'] ?? '',
            paymentType: $data['payment_type'] ?? '',
            grossAmount: (int) ($data['gross_amount'] ?? 0),
            fraudStatus: $data['fraud_status'] ?? null,
            vaNumber: $vaNumber,
            bank: $bank,
            store: $data['store'] ?? null,
            paymentCode: $data['payment_code'] ?? null,
            qrUrl: $data['actions'][0]['url'] ?? null,
            deeplinkUrl: $data['actions'][1]['url'] ?? null,
            signatureKey: $data['signature_key'] ?? null,
            statusCode: $data['status_code'] ?? null,
            statusMessage: $data['status_message'] ?? null,
            rawResponse: $data,
        );
    }

    /**
     * Apakah pembayaran berhasil (settlement/capture).
     */
    public function isSuccess(): bool
    {
        return in_array($this->transactionStatus, ['settlement', 'capture'])
            && ($this->fraudStatus === null || $this->fraudStatus === 'accept');
    }

    /**
     * Apakah pembayaran pending.
     */
    public function isPending(): bool
    {
        return $this->transactionStatus === 'pending';
    }

    /**
     * Apakah pembayaran gagal/dibatalkan/expired.
     */
    public function isFailed(): bool
    {
        return in_array($this->transactionStatus, ['deny', 'cancel', 'expire', 'failure']);
    }
}
