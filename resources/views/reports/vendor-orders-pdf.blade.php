<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Order Vendor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 20px;
            color: #16a34a;
            margin-bottom: 2px;
        }
        .header h2 {
            font-size: 14px;
            color: #555;
            font-weight: normal;
        }
        .header .subtitle {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
        }
        .filter-info {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .filter-info table,
        .summary table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .filter-info td {
            padding: 2px 8px;
            font-size: 10px;
        }
        .filter-info .label {
            width: 140px;
            font-weight: bold;
            color: #555;
        }
        .summary {
            margin-bottom: 14px;
        }
        .summary td {
            text-align: center;
            padding: 8px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .summary .value {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }
        .summary .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table thead th {
            background-color: #16a34a;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table thead th.text-right,
        .data-table tbody .text-right,
        .data-table tfoot .text-right {
            text-align: right;
        }
        .data-table thead th.text-center,
        .data-table tbody .text-center {
            text-align: center;
        }
        .data-table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 9px;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .data-table tfoot td {
            padding: 8px;
            font-weight: bold;
            border-top: 2px solid #16a34a;
            font-size: 10px;
        }
        .item-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .item-list li {
            padding: 1px 0;
            font-size: 8px;
            color: #555;
        }
        .item-list li:before {
            content: "- ";
            color: #999;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-processed { background-color: #dbeafe; color: #1d4ed8; }
        .badge-shipped { background-color: #e0e7ff; color: #3730a3; }
        .badge-delivered { background-color: #ede9fe; color: #6d28d9; }
        .badge-completed { background-color: #dcfce7; color: #166534; }
        .badge-payment-pending { background-color: #fef3c7; color: #92400e; }
        .badge-payment-waiting { background-color: #dbeafe; color: #1e40af; }
        .badge-payment-paid { background-color: #dcfce7; color: #166534; }
        .badge-payment-failed { background-color: #fee2e2; color: #991b1b; }
        .empty-state {
            text-align: center;
            color: #666;
            padding: 16px 8px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ORDER VENDOR</h1>
        <h2>{{ $vendorName }}</h2>
        <div class="subtitle">Dicetak pada: {{ $generatedAt }}</div>
    </div>

    <div class="filter-info">
        <table>
            <tr>
                <td class="label">Pencarian No. Order</td>
                <td>: {{ $search }}</td>
                <td class="label">Status Pesanan</td>
                <td>: {{ $status }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Data</td>
                <td>: {{ $orderVendors->count() }} pesanan</td>
                <td class="label">Toko</td>
                <td>: {{ $vendorName }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>
                    <div class="label">Total Pesanan</div>
                    <div class="value">{{ number_format($orderVendors->count()) }}</div>
                </td>
                <td>
                    <div class="label">Total Subtotal</div>
                    <div class="value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="label">Sudah Dibayar</div>
                    <div class="value" style="color: #16a34a;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="label">Belum Dibayar</div>
                    <div class="value" style="color: #dc2626;">Rp {{ number_format($totalAmount - $totalPaid, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 110px;">No. Order</th>
                <th style="width: 85px;">Tanggal</th>
                <th style="width: 110px;">Customer</th>
                <th>Item Produk</th>
                <th class="text-right" style="width: 90px;">Subtotal</th>
                <th class="text-center" style="width: 80px;">Status</th>
                <th class="text-center" style="width: 105px;">Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orderVendors as $index => $orderVendor)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 8px;">{{ $orderVendor->order?->order_number ?? '-' }}</td>
                    <td>{{ $orderVendor->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $orderVendor->order?->user?->name ?? '-' }}</td>
                    <td>
                        <ul class="item-list">
                            @foreach ($orderVendor->orderItems as $item)
                                <li>
                                    {{ $item->productVariant?->product?->name ?? '-' }}
                                    @if ($item->productVariant?->variant_name)
                                        ({{ $item->productVariant->variant_name }})
                                    @endif
                                    x{{ $item->quantity }}
                                    = Rp {{ number_format((float) $item->total, 0, ',', '.') }}
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        Rp {{ number_format((float) $orderVendor->subtotal, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($orderVendor->status?->value) {
                                'pending' => 'badge-pending',
                                'processed' => 'badge-processed',
                                'shipped' => 'badge-shipped',
                                'delivered' => 'badge-delivered',
                                'completed' => 'badge-completed',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $orderVendor->status?->getLabel() ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $paymentClass = match($orderVendor->order?->payment_status?->value) {
                                'pending' => 'badge-payment-pending',
                                'waiting_confirmation' => 'badge-payment-waiting',
                                'paid' => 'badge-payment-paid',
                                'failed' => 'badge-payment-failed',
                                default => 'badge-payment-pending',
                            };
                        @endphp
                        <span class="badge {{ $paymentClass }}">{{ $orderVendor->order?->payment_status?->getLabel() ?? '-' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-state">Tidak ada data order untuk filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">GRAND TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Laporan vendor digenerate otomatis oleh sistem - {{ $generatedAt }}
    </div>
</body>
</html>
