<?php

namespace App\Livewire\User\Dashboard;

use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
        if (! Auth::check()) {
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

        if (! $order || ! $order->payment) {
            session()->flash('error', 'Pesanan tidak ditemukan.');

            return;
        }

        $payment = $order->payment;

        if (! in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::Failed])) {
            session()->flash('error', 'Bukti pembayaran hanya bisa diunggah saat status menunggu pembayaran.');

            return;
        }

        $path = null;
        $oldPath = $payment->payment_proof;

        try {
            $path = $this->paymentProof->store('payments/proofs', 'public');

            DB::transaction(function () use ($order, $path): void {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedPayment = $lockedOrder->payment()->lockForUpdate()->first();

                if (! $lockedPayment || ! in_array($lockedPayment->status, [PaymentStatus::Pending, PaymentStatus::Failed], true)) {
                    throw ValidationException::withMessages([
                        'paymentProof' => 'Status pembayaran sudah berubah. Muat ulang halaman.',
                    ]);
                }

                $lockedPayment->update([
                    'payment_proof' => $path,
                    'status' => PaymentStatus::WaitingConfirmation,
                ]);
                $lockedOrder->update([
                    'payment_status' => OrderPaymentStatus::WaitingConfirmation,
                ]);
            });
        } catch (ValidationException $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Gagal menyimpan bukti pembayaran.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Bukti pembayaran gagal disimpan. Silakan coba lagi.');

            return;
        }

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        // Notify admin
        $adminUsers = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        foreach ($adminUsers as $admin) {
            try {
                $admin->notifications()->create([
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\PaymentProofUploaded',
                    'data' => json_encode([
                        'title' => 'Bukti Pembayaran Diunggah',
                        'message' => 'Pesanan #'.$order->order_number.' mengunggah bukti pembayaran.',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_name' => Auth::user()->name,
                    ]),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirim notifikasi bukti pembayaran.', [
                    'order_id' => $order->id,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
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
