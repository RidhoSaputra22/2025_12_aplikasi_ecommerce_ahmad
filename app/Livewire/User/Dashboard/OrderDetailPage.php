<?php

namespace App\Livewire\User\Dashboard;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderVendorStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderDetailPage extends Component
{
    use WithFileUploads;

    public ?int $orderId = null;
    public $paymentProofFile = null;

    public function mount(?int $orderId = null): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->orderId = $orderId;
    }

    public function getOrderProperty(): ?Order
    {
        if (!$this->orderId || !Auth::check()) {
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

    public function uploadPaymentProof(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->validate([
            'paymentProofFile' => ['required', 'image', 'max:2048'],
        ], [
            'paymentProofFile.required' => 'Bukti pembayaran wajib diunggah.',
            'paymentProofFile.image' => 'File harus berupa gambar.',
            'paymentProofFile.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $order = $this->order;

        if (!$order) {
            session()->flash('error', 'Pesanan tidak ditemukan.');
            return;
        }

        $payment = $order->payment;

        if (!$payment) {
            session()->flash('error', 'Data pembayaran tidak ditemukan.');
            return;
        }

        // Only allow upload for pending or failed payments
        if (!in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::Failed])) {
            session()->flash('error', 'Bukti pembayaran hanya bisa diunggah untuk pembayaran yang masih menunggu.');
            return;
        }

        // Store the file
        $path = $this->paymentProofFile->store('payments/proofs', 'public');

        // Delete old proof if exists
        if ($payment->payment_proof) {
            Storage::disk('public')->delete($payment->payment_proof);
        }

        // Update payment
        $payment->update([
            'payment_proof' => $path,
            'status' => PaymentStatus::WaitingConfirmation,
        ]);

        // Update order payment status
        $order->update([
            'payment_status' => OrderPaymentStatus::WaitingConfirmation,
        ]);

        // Send notification to admin
        $this->notifyAdmin($order);

        $this->paymentProofFile = null;
        session()->flash('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
    }

    protected function notifyAdmin(Order $order): void
    {
        // Find admin users and create notifications
        $adminUsers = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        foreach ($adminUsers as $admin) {
            $admin->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\PaymentProofUploaded',
                'data' => json_encode([
                    'title' => 'Bukti Pembayaran Diunggah',
                    'message' => 'Pesanan #' . $order->order_number . ' telah mengunggah bukti pembayaran.',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_name' => Auth::user()->name,
                ]),
            ]);
        }
    }

    /**
     * Customer mengkonfirmasi pesanan diterima untuk satu sub-order (per vendor).
     * Setelah konfirmasi, dana otomatis masuk ke wallet vendor.
     */
    public function confirmReceived(int $orderVendorId): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        $order = $this->order;
        if (!$order) {
            session()->flash('error', 'Pesanan tidak ditemukan.');
            return;
        }

        // Pastikan orderVendor ini milik order customer yang bersangkutan
        $orderVendor = OrderVendor::with(['vendor.vendorWallet', 'order'])
            ->where('id', $orderVendorId)
            ->where('order_id', $order->id)
            ->first();

        if (!$orderVendor) {
            session()->flash('error', 'Data sub-pesanan tidak ditemukan.');
            return;
        }

        if ($orderVendor->status !== OrderVendorStatus::Delivered) {
            session()->flash('error', 'Konfirmasi penerimaan hanya bisa dilakukan setelah vendor mengkonfirmasi pesanan tiba.');
            return;
        }

        // Update status orderVendor → completed
        $orderVendor->update([
            'status' => OrderVendorStatus::Completed,
            'customer_confirmed_at' => now(),
        ]);

        // Credit wallet vendor otomatis
        $this->creditVendorWallet($orderVendor);

        // Update status main order jika semua sub-order selesai
        $this->updateMainOrderStatus($orderVendor);

        // Notify vendor
        $this->notifyVendor($orderVendor);

        session()->flash('success', 'Penerimaan pesanan dikonfirmasi. Dana telah dikirim ke wallet vendor.');
    }

    protected function creditVendorWallet(OrderVendor $orderVendor): void
    {
        $vendor = $orderVendor->vendor;
        if (!$vendor) {
            return;
        }

        $wallet = $vendor->vendorWallet;
        if (!$wallet) {
            $wallet = $vendor->vendorWallet()->create(['balance' => 0]);
        }

        $amount = (float) $orderVendor->subtotal;
        $wallet->increment('balance', $amount);

        $wallet->transactions()->create([
            'vendor_wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => 'Pendapatan dari pesanan #' . $orderVendor->order?->order_number,
            'reference_id' => $orderVendor->id,
        ]);
    }

    protected function updateMainOrderStatus(OrderVendor $orderVendor): void
    {
        $order = $orderVendor->order;
        if (!$order) {
            return;
        }

        $allVendorOrders = $order->orderVendors()->get();

        if ($allVendorOrders->every(fn($ov) => $ov->status === OrderVendorStatus::Completed)) {
            $order->update(['status' => 'completed']);
        }
    }

    protected function notifyVendor(OrderVendor $orderVendor): void
    {
        $vendorUser = $orderVendor->vendor?->user;
        if (!$vendorUser) {
            return;
        }

        $vendorUser->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\OrderStatusUpdated',
            'data' => json_encode([
                'title' => 'Pesanan Selesai — Dana Dikirim',
                'message' => 'Pembeli telah mengkonfirmasi penerimaan pesanan #' . $orderVendor->order?->order_number . '. Dana telah masuk ke wallet Anda.',
                'order_id' => $orderVendor->order_id,
                'order_number' => $orderVendor->order?->order_number,
            ]),
        ]);
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
