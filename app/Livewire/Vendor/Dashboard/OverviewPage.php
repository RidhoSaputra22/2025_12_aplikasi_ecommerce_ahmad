<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Enums\OrderVendorStatus;
use App\Models\OrderVendor;
use App\Models\Product;
use App\Models\VendorWallet;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OverviewPage extends Component
{
    public function getVendorProperty()
    {
        return Auth::user()?->vendor;
    }

    public function render()
    {
        $vendor = $this->vendor;

        if (!$vendor) {
            return view('vendor.dashboard.overview-page', [
                'stats' => [],
                'recentOrders' => collect(),
            ]);
        }

        $totalProducts = Product::where('vendor_id', $vendor->id)->count();
        $activeProducts = Product::where('vendor_id', $vendor->id)->where('status', 'active')->count();

        $totalOrders = OrderVendor::where('vendor_id', $vendor->id)->count();
        $pendingOrders = OrderVendor::where('vendor_id', $vendor->id)->where('status', 'pending')->count();
        $processedOrders = OrderVendor::where('vendor_id', $vendor->id)->where('status', 'processed')->count();
        $shippedOrders = OrderVendor::where('vendor_id', $vendor->id)->where('status', 'shipped')->count();
        $completedOrders = OrderVendor::where('vendor_id', $vendor->id)->where('status', 'completed')->count();

        $wallet = VendorWallet::where('vendor_id', $vendor->id)->first();
        $balance = $wallet ? (float) $wallet->balance : 0;

        $totalRevenue = OrderVendor::where('vendor_id', $vendor->id)
            ->where('status', 'completed')
            ->sum('subtotal');

        $recentOrders = OrderVendor::with([
            'order.user',
            'orderItems.productVariant.product.productImages',
            'shipment',
        ])
            ->where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('vendor.dashboard.overview-page', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'activeProducts' => $activeProducts,
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'processedOrders' => $processedOrders,
                'shippedOrders' => $shippedOrders,
                'completedOrders' => $completedOrders,
                'balance' => $balance,
                'totalRevenue' => $totalRevenue,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }
}
