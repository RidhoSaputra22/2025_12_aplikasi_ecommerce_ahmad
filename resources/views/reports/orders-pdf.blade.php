<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Order</title>
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

        /* Header */
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

        /* Filter Info */
        .filter-info {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .filter-info table {
            width: 100%;
        }
        .filter-info td {
            padding: 2px 8px;
            font-size: 10px;
        }
        .filter-info .label {
            font-weight: bold;
            color: #555;
            width: 140px;
        }

        /* Summary */
        .summary {
            margin-bottom: 14px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
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

        /* Main Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
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
        .data-table thead th.text-right {
            text-align: right;
        }
        .data-table thead th.text-center {
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
        .data-table tbody .text-right {
            text-align: right;
        }
        .data-table tbody .text-center {
            text-align: center;
        }
        .data-table tfoot td {
            padding: 8px;
            font-weight: bold;
            border-top: 2px solid #16a34a;
            font-size: 10px;
        }

        /* Items sub-list */
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
            content: "• ";
            color: #999;
        }

        /* Vendor list */
        .vendor-name {
            font-size: 8px;
            color: #555;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-paid { background-color: #dbeafe; color: #1e40af; }
        .badge-shipped { background-color: #e0e7ff; color: #3730a3; }
        .badge-completed { background-color: #dcfce7; color: #166534; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
        .badge-waiting { background-color: #dbeafe; color: #1e40af; }
        .badge-failed { background-color: #fee2e2; color: #991b1b; }

        /* Footer */
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

        /* Page break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN ORDER</h1>
        <h2>E-Commerce Multi-Vendor</h2>
        <div class="subtitle">Dicetak pada: {{ $generatedAt }}</div>
    </div>

    <!-- Filter Info -->
    <div class="filter-info">
        <table>
            <tr>
                <td class="label">Periode</td>
                <td>: {{ $startDate }} s/d {{ $endDate }}</td>
                <td class="label">Status Order</td>
                <td>: {{ $status }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Data</td>
                <td>: {{ $orders->count() }} order</td>
                <td class="label">Status Pembayaran</td>
                <td>: {{ $paymentStatus }}</td>
            </tr>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary">
        <table>
            <tr>
                <td>
                    <div class="label">Total Order</div>
                    <div class="value">{{ number_format($orders->count()) }}</div>
                </td>
                <td>
                    <div class="label">Total Nilai Order</div>
                    <div class="value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="label">Total Terbayar</div>
                    <div class="value" style="color: #16a34a;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="label">Belum Terbayar</div>
                    <div class="value" style="color: #dc2626;">Rp {{ number_format($totalAmount - $totalPaid, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 100px;">No. Order</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 100px;">Customer</th>
                <th style="width: 100px;">Vendor</th>
                <th>Item Produk</th>
                <th class="text-right" style="width: 90px;">Total</th>
                <th class="text-center" style="width: 70px;">Status</th>
                <th class="text-center" style="width: 80px;">Pembayaran</th>
                <th class="text-center" style="width: 70px;">Gateway</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 8px;">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->user?->name ?? '-' }}</td>
                    <td>
                        @foreach($order->orderVendors as $ov)
                            <div class="vendor-name">{{ $ov->vendor?->store_name ?? '-' }}</div>
                        @endforeach
                    </td>
                    <td>
                        <ul class="item-list">
                            @foreach($order->orderVendors as $ov)
                                @foreach($ov->orderItems as $item)
                                    <li>
                                        {{ $item->productVariant?->product?->name ?? '-' }}
                                        @if($item->productVariant?->variant_name)
                                            ({{ $item->productVariant->variant_name }})
                                        @endif
                                        &times;{{ $item->quantity }}
                                        = Rp {{ number_format($item->total, 0, ',', '.') }}
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($order->status->value) {
                                'pending' => 'badge-pending',
                                'paid' => 'badge-paid',
                                'shipped' => 'badge-shipped',
                                'completed' => 'badge-completed',
                                'cancelled' => 'badge-cancelled',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $order->status->getLabel() }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $paymentClass = match($order->payment_status->value) {
                                'pending' => 'badge-pending',
                                'waiting_confirmation' => 'badge-waiting',
                                'paid' => 'badge-completed',
                                'failed' => 'badge-failed',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="badge {{ $paymentClass }}">{{ $order->payment_status->getLabel() }}</span>
                    </td>
                    <td class="text-center">
                        {{ $order->payment?->payment_gateway ? ucfirst($order->payment->payment_gateway) : 'Manual' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;">GRAND TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        Laporan digenerate otomatis oleh sistem &bull; {{ $generatedAt }}
    </div>
</body>
</html>
