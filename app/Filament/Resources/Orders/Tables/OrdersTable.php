<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\VendorWalletTransactionType;
use App\Models\VendorWallet;
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
                TextColumn::make('disbursement_status')
                    ->label('Pencairan')
                    ->getStateUsing(function ($record) {
                        $total = $record->orderVendors->count();
                        $disbursed = $record->orderVendors->where('is_disbursed', true)->count();
                        if ($total === 0) return '-';
                        if ($disbursed === $total) return '✅ Semua';
                        if ($disbursed > 0) return "⏳ {$disbursed}/{$total}";
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
                        $vendors = $record->orderVendors->where('is_disbursed', false);
                        $lines = ['Pesanan: '.$record->order_number, '', 'Vendor yang akan dicairkan:'];
                        foreach ($vendors as $ov) {
                            $vendorName = $ov->vendor?->store_name ?? 'Vendor #'.$ov->vendor_id;
                            $bankInfo = '';
                            $bankAccount = $ov->vendor?->vendorBankAccounts()?->where('is_active', true)->first();
                            if ($bankAccount) {
                                $bankInfo = " ({$bankAccount->bank_name} - {$bankAccount->account_number} a.n. {$bankAccount->account_holder})";
                            }
                            $lines[] = "• {$vendorName}: Rp ".number_format((float) $ov->subtotal, 0, ',', '.').$bankInfo;
                        }
                        return implode("\n", $lines);
                    })
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            $undisbursedVendors = $record->orderVendors()->where('is_disbursed', false)->get();

                            foreach ($undisbursedVendors as $orderVendor) {
                                // Get or create vendor wallet
                                $wallet = VendorWallet::firstOrCreate(
                                    ['vendor_id' => $orderVendor->vendor_id],
                                    ['balance' => 0]
                                );

                                // Add credit transaction
                                $wallet->transactions()->create([
                                    'type' => VendorWalletTransactionType::Credit->value,
                                    'amount' => $orderVendor->subtotal,
                                    'description' => 'Pencairan dari pesanan #'.$record->order_number,
                                    'reference_id' => 'ORDER-'.$record->id.'-VENDOR-'.$orderVendor->vendor_id,
                                ]);

                                // Update wallet balance
                                $wallet->increment('balance', $orderVendor->subtotal);

                                // Mark as disbursed
                                $orderVendor->update([
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
                                            'message' => 'Dana sebesar Rp '.number_format((float) $orderVendor->subtotal, 0, ',', '.').' dari pesanan #'.$record->order_number.' telah dicairkan ke wallet Anda.',
                                            'order_id' => $record->id,
                                            'order_number' => $record->order_number,
                                            'amount' => $orderVendor->subtotal,
                                        ]),
                                    ]);
                                }
                            }
                        });
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
