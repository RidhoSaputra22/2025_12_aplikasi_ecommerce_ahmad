<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
