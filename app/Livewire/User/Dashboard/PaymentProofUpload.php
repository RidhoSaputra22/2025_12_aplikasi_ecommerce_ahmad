<?php

namespace App\Livewire\User\Dashboard;

use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PaymentProofUpload extends Component
{
    use WithFileUploads;

    public ?int $orderId = null;
    public $paymentProof = null;

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId;
    }

    public function save(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->validate([
            'paymentProof' => ['required', 'image', 'max:2048'],
        ], [
            'paymentProof.required' => 'Pilih file bukti pembayaran.',
            'paymentProof.image' => 'File harus berupa gambar (jpg, png, dll).',
            'paymentProof.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $order = Order::with('payment')
            ->where('id', $this->orderId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order || !$order->payment) {
            session()->flash('error', 'Pesanan tidak ditemukan.');
            return;
        }

        $payment = $order->payment;

        if (!in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::Failed])) {
            session()->flash('error', 'Bukti pembayaran hanya bisa diunggah saat status menunggu pembayaran.');
            return;
        }

        // Store file
        $path = $this->paymentProof->store('payments/proofs', 'public');

        // Delete old file
        if ($payment->payment_proof) {
            Storage::disk('public')->delete($payment->payment_proof);
        }

        // Update payment
        $payment->update([
            'payment_proof' => $path,
            'status' => PaymentStatus::WaitingConfirmation,
        ]);

        // Update order
        $order->update([
            'payment_status' => OrderPaymentStatus::WaitingConfirmation,
        ]);

        // Notify admin
        $adminUsers = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        foreach ($adminUsers as $admin) {
            $admin->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\\Notifications\\PaymentProofUploaded',
                'data' => json_encode([
                    'title' => 'Bukti Pembayaran Diunggah',
                    'message' => 'Pesanan #' . $order->order_number . ' mengunggah bukti pembayaran.',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_name' => Auth::user()->name,
                ]),
            ]);
        }

        $this->dispatch('payment-proof:uploaded');
        $this->dispatch('forceCloseModal');

        session()->flash('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
    }

    public function render()
    {
        return view('livewire.user.dashboard.payment-proof-upload');
    }
}
