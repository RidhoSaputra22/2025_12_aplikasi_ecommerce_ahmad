<?php

namespace App\DataTransfers\Payment;

/**
 * DTO untuk data transaksi Snap Midtrans.
 *
 * Menyimpan data yang diperlukan untuk membuat Snap Token,
 * termasuk detail transaksi, item, customer, dan konfigurasi pembayaran.
 */
final readonly class SnapTransactionData
{
    /**
     * @param string $orderNumber     Nomor order unik
     * @param int    $grossAmount     Total jumlah pembayaran (dalam Rupiah, integer)
     * @param array  $itemDetails     Detail item [{id, price, quantity, name, merchant_name}]
     * @param array  $customerDetails Data customer {first_name, email, phone, billing_address, shipping_address}
     * @param array  $enabledPayments Metode pembayaran yang diaktifkan
     * @param int    $expiryDuration  Durasi kedaluwarsa pembayaran (menit)
     * @param array  $callbacks       URL callback {finish, error, pending}
     */
    public function __construct(
        public string $orderNumber,
        public int    $grossAmount,
        public array  $itemDetails = [],
        public array  $customerDetails = [],
        public array  $enabledPayments = [],
        public int    $expiryDuration = 1440,
        public array  $callbacks = [],
    ) {}

    /**
     * Konversi ke format parameter Midtrans Snap.
     */
    public function toMidtransParams(): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $this->orderNumber,
                'gross_amount' => $this->grossAmount,
            ],
        ];

        if (!empty($this->itemDetails)) {
            $params['item_details'] = $this->itemDetails;
        }

        if (!empty($this->customerDetails)) {
            $params['customer_details'] = $this->customerDetails;
        }

        if (!empty($this->enabledPayments)) {
            $params['enabled_payments'] = $this->enabledPayments;
        }

        if ($this->expiryDuration > 0) {
            $params['expiry'] = [
                'unit' => 'minutes',
                'duration' => $this->expiryDuration,
            ];
        }

        if (!empty($this->callbacks)) {
            $params['callbacks'] = $this->callbacks;
        }

        // Credit card 3DS
        $params['credit_card'] = [
            'secure' => true,
        ];

        return $params;
    }
}
