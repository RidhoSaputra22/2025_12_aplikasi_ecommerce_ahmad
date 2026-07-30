<?php

namespace App\Livewire\User\Dashboard;

use App\Enums\OrderVendorStatus;
use App\Enums\VendorWalletTransactionType;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\VendorWallet;
use App\Services\AdminFeeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderDetailPage extends Component
{
    public ?int $orderId = null;

    public function mount(?int $orderId = null): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        $this->orderId = $orderId;
    }

    public function getOrderProperty(): ?Order
    {
        if (! $this->orderId || ! Auth::check()) {
            return null;
        }

        return Order::with([
            'payment',
            'orderVendors.vendor',
            'orderVendors.orderItems.productVariant.product.productImages',
            'orderVendors.orderItems.productVariant.product.category',
            'orderVendors.shipment.shipmentAddress',
            'orderVendors.shipment.shipmentCourier',
        ])
            ->where('id', $this->orderId)
            ->where('user_id', Auth::id())
            ->first();
    }

    /**
     * Customer mengkonfirmasi pesanan diterima untuk satu sub-order (per vendor).
     * Setelah konfirmasi, dana otomatis masuk ke wallet vendor.
     */
    public function confirmReceived(int $orderVendorId): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        $order = $this->order;
        if (! $order) {
            session()->flash('error', 'Pesanan tidak ditemukan.');

            return;
        }

        $orderVendor = DB::transaction(function () use ($orderVendorId, $order): ?OrderVendor {
            $lockedOrderVendor = OrderVendor::query()
                ->with(['vendor', 'order'])
                ->whereKey($orderVendorId)
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrderVendor || $lockedOrderVendor->status !== OrderVendorStatus::Delivered) {
                return null;
            }

            $lockedOrderVendor->update([
                'status' => OrderVendorStatus::Completed,
                'customer_confirmed_at' => now(),
            ]);

            $this->creditVendorWallet($lockedOrderVendor);
            $this->updateMainOrderStatus($lockedOrderVendor);

            return $lockedOrderVendor->fresh(['vendor.user', 'order']);
        });

        if (! $orderVendor) {
            session()->flash('error', 'Konfirmasi penerimaan hanya bisa dilakukan satu kali setelah pesanan tiba.');

            return;
        }

        $this->notifyVendor($orderVendor);

        session()->flash('success', 'Penerimaan pesanan dikonfirmasi. Dana telah dikirim ke wallet vendor.');
    }

    protected function creditVendorWallet(OrderVendor $orderVendor): void
    {
        if ($orderVendor->is_disbursed) {
            return;
        }

        $breakdown = app(AdminFeeService::class)->resolveBreakdown($orderVendor);
        $wallet = VendorWallet::query()
            ->where('vendor_id', $orderVendor->vendor_id)
            ->lockForUpdate()
            ->first();

        if (! $wallet) {
            $wallet = VendorWallet::query()->create([
                'vendor_id' => $orderVendor->vendor_id,
                'balance' => 0,
            ]);
        }

        $amount = (float) $breakdown['vendor_payout_amount'];
        $wallet->increment('balance', $amount);

        $wallet->transactions()->create([
            'type' => VendorWalletTransactionType::Credit,
            'amount' => $amount,
            'description' => 'Pendapatan bersih dari pesanan #'.$orderVendor->order?->order_number,
            'reference_id' => 'ORDER-'.$orderVendor->order_id.'-VENDOR-'.$orderVendor->vendor_id,
        ]);

        $orderVendor->update([
            'admin_fee_percentage' => $breakdown['admin_fee_percentage'],
            'admin_fee_amount' => $breakdown['admin_fee_amount'],
            'vendor_payout_amount' => $breakdown['vendor_payout_amount'],
            'is_disbursed' => true,
            'disbursed_at' => now(),
        ]);
    }

    protected function updateMainOrderStatus(OrderVendor $orderVendor): void
    {
        $order = $orderVendor->order;
        if (! $order) {
            return;
        }

        $allVendorOrders = $order->orderVendors()->get();

        if ($allVendorOrders->every(fn ($ov) => $ov->status === OrderVendorStatus::Completed)) {
            $order->update(['status' => 'completed']);
        }
    }

    protected function notifyVendor(OrderVendor $orderVendor): void
    {
        $vendorUser = $orderVendor->vendor?->user;
        if (! $vendorUser) {
            return;
        }

        try {
            $vendorUser->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\\Notifications\\OrderStatusUpdated',
                'data' => json_encode([
                    'title' => 'Pesanan Selesai — Dana Dikirim',
                    'message' => 'Pembeli telah mengkonfirmasi penerimaan pesanan #'.$orderVendor->order?->order_number.'. Dana telah masuk ke wallet Anda.',
                    'order_id' => $orderVendor->order_id,
                    'order_number' => $orderVendor->order?->order_number,
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi penyelesaian pesanan.', [
                'order_vendor_id' => $orderVendor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function openPaymentProofModal(): void
    {
        $this->dispatch(
            'openModal',
            component: 'user.dashboard.payment-proof-upload',
            arguments: ['orderId' => $this->orderId],
            title: 'Upload Bukti Pembayaran',
            maxWidth: '3xl',
        );
    }

    #[On('payment-proof:uploaded')]
    public function onPaymentProofUploaded(): void
    {
        // Refresh the page data
    }

    public function render()
    {
        return view('user.dashboard.order-detail-page');
    }
}
