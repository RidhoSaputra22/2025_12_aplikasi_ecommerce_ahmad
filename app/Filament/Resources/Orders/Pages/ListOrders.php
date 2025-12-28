<?php

namespace App\Filament\Resources\Orders\Pages;

use Closure;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\UserRole;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Models\OrderVendor;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\ShipmentStatus;
use App\Models\ProductVariant;
use App\Models\ShipmentAddress;
use App\Enums\OrderVendorStatus;

use App\Enums\OrderPaymentStatus;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Forms\Components\CardRadio;
use Filament\Schemas\Components\Wizard\Step;
use App\Filament\Resources\Users\UserResource;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use Filament\Forms\Components\Repeater\TableColumn;

/**
 * ListOrders Page
 *
 * Halaman untuk menampilkan daftar orders dan membuat order baru melalui wizard
 * dengan langkah: pilih produk, pengiriman, dan pembayaran
 */
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * Mendapatkan aksi header yang tersedia di halaman list orders
     *
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Order Baru'),
        ];
    }
}