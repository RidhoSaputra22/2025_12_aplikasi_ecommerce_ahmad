<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderVendorStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderVendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderReportPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $vendor = Auth::user()?->vendor;

        abort_unless($vendor, 403);

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $statusEnum = filled($status) ? OrderVendorStatus::tryFrom($status) : null;

        $orderVendors = OrderVendor::query()
            ->with([
                'order.user',
                'order.payment',
                'orderItems.productVariant.product',
            ])
            ->where('vendor_id', $vendor->id)
            ->when(
                $statusEnum,
                fn (Builder $query) => $query->where('status', $statusEnum)
            )
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->whereHas('order', function (Builder $orderQuery) use ($search) {
                    $orderQuery->where('order_number', 'like', '%' . $search . '%');
                });
            })
            ->latest('created_at')
            ->get();

        $totalAmount = (float) $orderVendors->sum('subtotal');
        $totalPaid = (float) $orderVendors
            ->filter(fn (OrderVendor $orderVendor) => $orderVendor->order?->payment_status === OrderPaymentStatus::Paid)
            ->sum('subtotal');

        $pdf = Pdf::loadView('reports.vendor-orders-pdf', [
            'orderVendors' => $orderVendors,
            'vendorName' => $vendor->store_name ?? 'Vendor',
            'search' => $search !== '' ? $search : 'Semua No. Order',
            'status' => $statusEnum?->getLabel() ?? 'Semua Status',
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-order-vendor-' . Str::slug($vendor->store_name ?? 'vendor') . '-' . now()->format('Ymd_His') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
