<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\DataTransfers\Payment\MidtransCallbackData;
use App\DataTransfers\Payment\SnapTransactionData;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service untuk mengelola business logic pembayaran.
 *
 * Class ini bertanggung jawab untuk:
 * - Membuat pembayaran dengan Midtrans Snap
 * - Memproses callback/notification dari Midtrans
 * - Mengupdate status pembayaran & order
 * - Membangun data transaksi dari Order
 *
 * Business logic pembayaran dipisahkan dari gateway-specific logic
 * yang ada di MidtransService.
 */
class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    /**
     * Buat pembayaran Midtrans untuk sebuah order.
     *
     * @return array{token: string, redirect_url: string}
     *
     * @throws \Exception
     */
    public function createMidtransPayment(Order $order): array
    {
        $order->loadMissing([
            'user',
            'orderVendors.orderItems.productVariant.product',
            'orderVendors.vendor',
            'orderVendors.shipment.shipmentAddress',
        ]);

        $transactionData = $this->buildSnapTransactionData($order);
        $result = $this->gateway->createTransaction($transactionData);

        // Update payment record dengan snap token
        $order->payment()->update([
            'payment_method' => 'midtrans',
            'payment_gateway' => 'midtrans',
            'snap_token' => $result['token'],
            'snap_redirect_url' => $result['redirect_url'],
            'expired_at' => now()->addMinutes(config('midtrans.payment_expiry_duration', 1440)),
        ]);

        return $result;
    }

    /**
     * Proses notification/callback dari Midtrans.
     *
     * Method ini dipanggil oleh webhook controller.
     * Menggunakan DB transaction untuk memastikan konsistensi data.
     */
    public function processNotification(array $payload): void
    {
        $callbackData = $this->gateway->handleNotification($payload);

        DB::transaction(function () use ($callbackData) {
            $payment = Payment::query()
                ->whereHas('order', fn ($q) => $q->where('order_number', $callbackData->orderId))
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                Log::channel('payment')->warning('Payment not found for notification', [
                    'order_id' => $callbackData->orderId,
                ]);
                return;
            }

            // Hindari proses ulang jika sudah success
            if ($payment->status === PaymentStatus::Success) {
                Log::channel('payment')->info('Payment already settled, skipping', [
                    'order_id' => $callbackData->orderId,
                ]);
                return;
            }

            $this->updatePaymentFromCallback($payment, $callbackData);
            $this->updateOrderFromCallback($payment->order, $callbackData);

            Log::channel('payment')->info('Notification processed successfully', [
                'order_id' => $callbackData->orderId,
                'status' => $callbackData->transactionStatus,
            ]);
        });
    }

    /**
     * Cek dan sinkronkan status pembayaran dari Midtrans.
     *
     * Berguna untuk manual check atau cron job.
     */
    public function syncPaymentStatus(Order $order): void
    {
        try {
            $callbackData = $this->gateway->getTransactionStatus($order->order_number);

            DB::transaction(function () use ($order, $callbackData) {
                $payment = $order->payment()->lockForUpdate()->first();

                if (!$payment || $payment->status === PaymentStatus::Success) {
                    return;
                }

                $this->updatePaymentFromCallback($payment, $callbackData);
                $this->updateOrderFromCallback($order, $callbackData);
            });

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to sync payment status', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Batalkan pembayaran Midtrans.
     */
    public function cancelPayment(Order $order): bool
    {
        try {
            $this->gateway->cancelTransaction($order->order_number);

            $order->payment()->update([
                'status' => PaymentStatus::Failed,
            ]);

            $order->update([
                'payment_status' => OrderPaymentStatus::Failed,
                'status' => OrderStatus::Cancelled,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::channel('payment')->error('Failed to cancel payment', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Bangun SnapTransactionData dari Order.
     */
    private function buildSnapTransactionData(Order $order): SnapTransactionData
    {
        $itemDetails = [];
        $calculatedTotal = 0;

        foreach ($order->orderVendors as $orderVendor) {
            foreach ($orderVendor->orderItems as $item) {
                $product = $item->productVariant?->product;
                $variant = $item->productVariant;
                $itemName = ($product?->name ?? 'Produk');

                if ($variant?->variant_name) {
                    $itemName .= ' - ' . $variant->variant_name;
                }

                // Midtrans membutuhkan nama item <= 50 karakter
                $itemName = Str::limit($itemName, 50);

                $price = (int) $item->price;
                $qty = (int) $item->quantity;

                $itemDetails[] = [
                    'id' => (string) $item->id,
                    'price' => $price,
                    'quantity' => $qty,
                    'name' => $itemName,
                    'merchant_name' => Str::limit($orderVendor->vendor?->store_name ?? 'Vendor', 25),
                ];

                $calculatedTotal += $price * $qty;
            }

            // Tambahkan ongkir per vendor sebagai item jika ada
            $shipment = $orderVendor->shipment;
            if ($shipment && (int) $shipment->shipping_cost > 0) {
                $shippingCost = (int) $shipment->shipping_cost;
                $itemDetails[] = [
                    'id' => 'SHIP-' . $orderVendor->id,
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Ongkir ' . Str::limit($orderVendor->vendor?->store_name ?? '', 35),
                    'merchant_name' => Str::limit($orderVendor->vendor?->store_name ?? 'Vendor', 25),
                ];
                $calculatedTotal += $shippingCost;
            }
        }

        // Gunakan gross_amount dari calculated total agar cocok dengan item_details
        $grossAmount = $calculatedTotal > 0 ? $calculatedTotal : (int) $order->total_amount;

        $user = $order->user;
        $shippingAddress = $order->orderVendors->first()?->shipment?->shipmentAddress;

        $customerDetails = [
            'first_name' => $user?->name ?? 'Customer',
            'email' => $user?->email ?? 'customer@example.com',
            'phone' => $user?->phone ?? '',
        ];

        if ($shippingAddress) {
            $addressData = [
                'first_name' => $user?->name ?? 'Customer',
                'phone' => $user?->phone ?? '',
                'address' => $shippingAddress->address ?? '',
                'city' => $shippingAddress->city ?? '',
                'postal_code' => $shippingAddress->postal_code ?? '',
                'country_code' => 'IDN',
            ];

            $customerDetails['shipping_address'] = $addressData;
            $customerDetails['billing_address'] = $addressData;
        }

        return new SnapTransactionData(
            orderNumber: $order->order_number,
            grossAmount: $grossAmount,
            itemDetails: $itemDetails,
            customerDetails: $customerDetails,
            enabledPayments: config('midtrans.enabled_payments', []),
            expiryDuration: config('midtrans.payment_expiry_duration', 1440),
            callbacks: [
                'finish' => route('payment.finish'),
            ],
        );
    }

    /**
     * Update data Payment dari callback Midtrans.
     */
    private function updatePaymentFromCallback(Payment $payment, MidtransCallbackData $data): void
    {
        $updateData = [
            'midtrans_transaction_id' => $data->transactionId,
            'midtrans_payment_type' => $data->paymentType,
            'midtrans_fraud_status' => $data->fraudStatus,
            'midtrans_raw_response' => $data->rawResponse,
            'transaction_reference' => $data->transactionId,
        ];

        // Simpan detail pembayaran spesifik
        if ($data->vaNumber) {
            $updateData['midtrans_va_number'] = $data->vaNumber;
            $updateData['midtrans_bank'] = $data->bank;
        }

        if ($data->store) {
            $updateData['midtrans_store'] = $data->store;
            $updateData['midtrans_payment_code'] = $data->paymentCode;
        }

        if ($data->qrUrl) {
            $updateData['midtrans_qr_url'] = $data->qrUrl;
        }

        if ($data->deeplinkUrl) {
            $updateData['midtrans_deeplink_url'] = $data->deeplinkUrl;
        }

        // Tentukan status pembayaran
        if ($data->isSuccess()) {
            $updateData['status'] = PaymentStatus::Success;
            $updateData['paid_at'] = now();
            $updateData['confirmed_at'] = now();
        } elseif ($data->isPending()) {
            $updateData['status'] = PaymentStatus::Pending;
        } elseif ($data->isFailed()) {
            $updateData['status'] = PaymentStatus::Failed;
        }

        $payment->update($updateData);
    }

    /**
     * Update status Order berdasarkan callback Midtrans.
     */
    private function updateOrderFromCallback(Order $order, MidtransCallbackData $data): void
    {
        if ($data->isSuccess()) {
            $order->update([
                'payment_status' => OrderPaymentStatus::Paid,
                'status' => OrderStatus::Paid,
            ]);

            // Update semua order vendor ke processing
            $order->orderVendors()->update([
                'status' => OrderVendorStatus::Processing,
            ]);

            // Kirim notifikasi ke vendor
            $this->notifyVendorsOrderPaid($order);

        } elseif ($data->isFailed()) {
            $order->update([
                'payment_status' => OrderPaymentStatus::Failed,
                'status' => OrderStatus::Cancelled,
            ]);

            // Rollback stock
            $this->rollbackStock($order);
        }
    }

    /**
     * Notifikasi ke vendor bahwa order sudah dibayar.
     */
    private function notifyVendorsOrderPaid(Order $order): void
    {
        $order->loadMissing('orderVendors.vendor.user');

        foreach ($order->orderVendors as $orderVendor) {
            $vendorUser = $orderVendor->vendor?->user;

            if (!$vendorUser) {
                continue;
            }

            try {
                $vendorUser->notifications()->create([
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\OrderPaidNotification',
                    'data' => json_encode([
                        'title' => 'Pesanan Baru Dibayar',
                        'message' => 'Pesanan #' . $order->order_number . ' telah dibayar. Silakan proses pesanan.',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_vendor_id' => $orderVendor->id,
                    ]),
                ]);
            } catch (\Exception $e) {
                Log::channel('payment')->warning('Failed to notify vendor', [
                    'vendor_id' => $orderVendor->vendor_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Rollback stok produk saat pembayaran gagal.
     */
    private function rollbackStock(Order $order): void
    {
        $order->loadMissing('orderVendors.orderItems.productVariant');

        foreach ($order->orderVendors as $orderVendor) {
            foreach ($orderVendor->orderItems as $item) {
                $variant = $item->productVariant;

                if ($variant) {
                    $variant->increment('stock', (int) $item->quantity);
                }
            }
        }

        Log::channel('payment')->info('Stock rolled back for cancelled order', [
            'order_number' => $order->order_number,
        ]);
    }
}
