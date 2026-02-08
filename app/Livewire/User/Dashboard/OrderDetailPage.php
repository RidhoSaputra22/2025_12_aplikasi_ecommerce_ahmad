<?php

namespace App\Livewire\User\Dashboard;

use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
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
