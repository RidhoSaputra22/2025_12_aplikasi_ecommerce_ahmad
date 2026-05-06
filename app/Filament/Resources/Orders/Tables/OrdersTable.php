<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\VendorWalletTransactionType;
use App\Models\VendorWallet;
use App\Services\AdminFeeService;
use App\Services\Payment\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['orderVendors.vendor.vendorBankAccounts']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->sortable(),
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('Total Harga')
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('admin_fee_total')
                    ->label('Potongan Admin')
                    ->getStateUsing(fn ($record) => app(AdminFeeService::class)->getOrderAdminFeeTotal($record))
                    ->money('IDR')
                    ->toggleable(),
                TextColumn::make('vendor_payout_total')
                    ->label('Diterima Vendor')
                    ->getStateUsing(fn ($record) => app(AdminFeeService::class)->getOrderVendorPayoutTotal($record))
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge(),
                TextColumn::make('payment.payment_proof')
                    ->label('Bukti Bayar')
                    ->formatStateUsing(fn ($state) => $state ? '✅ Ada' : '❌ Belum')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('payment.payment_gateway')
                    ->label('Gateway')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Manual')
                    ->badge()
                    ->color(fn ($state) => $state === 'midtrans' ? 'info' : 'gray')
                    ->toggleable(),
                TextColumn::make('payment.midtrans_payment_type')
                    ->label('Tipe Bayar')
                    ->formatStateUsing(fn ($state) => $state ? ucwords(str_replace('_', ' ', $state)) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('disbursement_status')
                    ->label('Pencairan')
                    ->getStateUsing(function ($record) {
                        $total = $record->orderVendors->count();
                        $disbursed = $record->orderVendors->where('is_disbursed', true)->count();
                        if ($total === 0) {
                            return '-';
                        }
                        if ($disbursed === $total) {
                            return '✅ Semua';
                        }
                        if ($disbursed > 0) {
                            return "⏳ {$disbursed}/{$total}";
                        }

                        return '❌ Belum';
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->label('Status Order'),
                SelectFilter::make('payment_status')
                    ->options(OrderPaymentStatus::class)
                    ->label('Status Pembayaran'),
            ])
            ->recordActions([
                Action::make('confirm_payment')
                    ->label('Konfirmasi Bayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->payment_status === OrderPaymentStatus::WaitingConfirmation)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription(fn ($record) => 'Apakah Anda yakin ingin mengkonfirmasi pembayaran untuk pesanan '.$record->order_number.'?')
                    ->action(function ($record) {
                        $payment = $record->payment;
                        if ($payment) {
                            $payment->update([
                                'status' => PaymentStatus::Success,
                                'confirmed_at' => now(),
                                'confirmed_by' => Auth::id(),
                                'paid_at' => now(),
                            ]);
                        }

                        $record->update([
                            'status' => OrderStatus::Paid,
                            'payment_status' => OrderPaymentStatus::Paid,
                        ]);

                        // Notify customer
                        $record->user->notifications()->create([
                            'id' => Str::uuid(),
                            'type' => 'App\\Notifications\\PaymentConfirmed',
                            'data' => json_encode([
                                'title' => 'Pembayaran Dikonfirmasi',
                                'message' => 'Pembayaran untuk pesanan #'.$record->order_number.' telah dikonfirmasi oleh admin.',
                                'order_id' => $record->id,
                                'order_number' => $record->order_number,
                            ]),
                        ]);
                    }),
                Action::make('reject_payment')
                    ->label('Tolak Bayar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->payment_status === OrderPaymentStatus::WaitingConfirmation)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pembayaran')
                    ->modalDescription(fn ($record) => 'Apakah Anda yakin ingin menolak pembayaran untuk pesanan '.$record->order_number.'? Customer harus upload ulang bukti pembayaran.')
                    ->action(function ($record) {
                        $payment = $record->payment;
                        if ($payment) {
                            $payment->update([
                                'status' => PaymentStatus::Failed,
                            ]);
                        }

                        $record->update([
                            'payment_status' => OrderPaymentStatus::Failed,
                        ]);

                        // Notify customer
                        $record->user->notifications()->create([
                            'id' => Str::uuid(),
                            'type' => 'App\\Notifications\\PaymentRejected',
                            'data' => json_encode([
                                'title' => 'Pembayaran Ditolak',
                                'message' => 'Pembayaran untuk pesanan #'.$record->order_number.' ditolak. Silakan upload ulang bukti pembayaran yang valid.',
                                'order_id' => $record->id,
                                'order_number' => $record->order_number,
                            ]),
                        ]);
                    }),
                Action::make('disburse_to_vendors')
                    ->label('Cairkan ke Vendor')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(function ($record) {
                        // Show only when order is completed and has undisbursed vendors
                        if ($record->status !== OrderStatus::Completed) {
                            return false;
                        }

                        return $record->orderVendors->where('is_disbursed', false)->count() > 0;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cairkan Dana ke Vendor')
                    ->modalDescription(function ($record) {
                        $adminFeeService = app(AdminFeeService::class);
                        $vendors = $record->orderVendors->where('is_disbursed', false);
                        $lines = [
                            'Pesanan: '.$record->order_number,
                            'Catatan: potongan admin dihitung dari subtotal produk vendor, tidak termasuk ongkir.',
                            '',
                            'Vendor yang akan dicairkan:',
                        ];
                        foreach ($vendors as $ov) {
                            $breakdown = $adminFeeService->resolveBreakdown($ov);
                            $vendorName = $ov->vendor?->store_name ?? 'Vendor #'.$ov->vendor_id;
                            $bankInfo = '';
                            $bankAccount = $ov->vendor?->vendorBankAccounts()?->where('is_active', true)->first();
                            if ($bankAccount) {
                                $bankInfo = " ({$bankAccount->bank_name} - {$bankAccount->account_number} a.n. {$bankAccount->account_holder})";
                            }
                            $lines[] = "• {$vendorName}: subtotal Rp ".number_format((float) $breakdown['gross_amount'], 0, ',', '.')
                                .', potongan admin '.number_format((float) $breakdown['admin_fee_percentage'], 2, ',', '.').'%'
                                .' = Rp '.number_format((float) $breakdown['admin_fee_amount'], 0, ',', '.')
                                .', vendor terima Rp '.number_format((float) $breakdown['vendor_payout_amount'], 0, ',', '.')
                                .$bankInfo;
                        }

                        return implode("\n", $lines);
                    })
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            $adminFeeService = app(AdminFeeService::class);
                            $undisbursedVendors = $record->orderVendors()->where('is_disbursed', false)->get();

                            foreach ($undisbursedVendors as $orderVendor) {
                                $breakdown = $adminFeeService->resolveBreakdown($orderVendor);

                                if (
                                    $orderVendor->admin_fee_percentage === null
                                    || $orderVendor->admin_fee_amount === null
                                    || $orderVendor->vendor_payout_amount === null
                                ) {
                                    $orderVendor = $adminFeeService->syncOrderVendor($orderVendor);
                                    $breakdown = $adminFeeService->resolveBreakdown($orderVendor);
                                }

                                // Get or create vendor wallet
                                $wallet = VendorWallet::firstOrCreate(
                                    ['vendor_id' => $orderVendor->vendor_id],
                                    ['balance' => 0]
                                );

                                // Add credit transaction
                                $wallet->transactions()->create([
                                    'type' => VendorWalletTransactionType::Credit->value,
                                    'amount' => $breakdown['vendor_payout_amount'],
                                    'description' => 'Pencairan bersih dari pesanan #'.$record->order_number
                                        .' (subtotal Rp '.number_format((float) $breakdown['gross_amount'], 0, ',', '.')
                                        .', potongan admin Rp '.number_format((float) $breakdown['admin_fee_amount'], 0, ',', '.').')',
                                    'reference_id' => 'ORDER-'.$record->id.'-VENDOR-'.$orderVendor->vendor_id,
                                ]);

                                // Update wallet balance
                                $wallet->increment('balance', $breakdown['vendor_payout_amount']);

                                // Mark as disbursed
                                $orderVendor->update([
                                    'admin_fee_percentage' => $breakdown['admin_fee_percentage'],
                                    'admin_fee_amount' => $breakdown['admin_fee_amount'],
                                    'vendor_payout_amount' => $breakdown['vendor_payout_amount'],
                                    'is_disbursed' => true,
                                    'disbursed_at' => now(),
                                    'disbursed_by' => Auth::id(),
                                ]);

                                // Notify vendor
                                $vendorUser = $orderVendor->vendor?->user;
                                if ($vendorUser) {
                                    $vendorUser->notifications()->create([
                                        'id' => Str::uuid(),
                                        'type' => 'App\\Notifications\\VendorDisbursement',
                                        'data' => json_encode([
                                            'title' => 'Dana Dicairkan',
                                            'message' => 'Dana bersih sebesar Rp '.number_format((float) $breakdown['vendor_payout_amount'], 0, ',', '.')
                                                .' dari pesanan #'.$record->order_number.' telah dicairkan ke wallet Anda setelah potongan admin Rp '
                                                .number_format((float) $breakdown['admin_fee_amount'], 0, ',', '.').'.',
                                            'order_id' => $record->id,
                                            'order_number' => $record->order_number,
                                            'amount' => $breakdown['vendor_payout_amount'],
                                            'gross_amount' => $breakdown['gross_amount'],
                                            'admin_fee_amount' => $breakdown['admin_fee_amount'],
                                            'admin_fee_percentage' => $breakdown['admin_fee_percentage'],
                                        ]),
                                    ]);
                                }
                            }
                        });
                    }),
                Action::make('sync_midtrans')
                    ->label('Sync Midtrans')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => $record->payment?->payment_gateway === 'midtrans'
                        && in_array($record->payment?->status, [PaymentStatus::Pending])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronkan Status Midtrans')
                    ->modalDescription('Cek status terbaru pembayaran dari Midtrans.')
                    ->action(function ($record) {
                        try {
                            $paymentService = app(PaymentService::class);
                            $paymentService->syncPaymentStatus($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Status Midtrans berhasil disinkronkan.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal sinkronkan: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
